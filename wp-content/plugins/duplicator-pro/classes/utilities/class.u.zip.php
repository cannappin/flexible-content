<?php
/**
 * Utility class for zipping up content
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package DUP_PRO
 * @subpackage classes/utilities
 * @copyright (c) 2017, Snapcreek LLC
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since 3.3.0
 */

defined("ABSPATH") or die("");

use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapUtil;

/**
 * Helper class for reporting problems with zipping
 *
 * @see  DUP_PRO_Zip_U
 */
class DUP_PRO_Problem_Fix
{

    /**
     * The detected problem
     */
    public $problem = '';
/**
     * A recommended fix for the problem
     */
    public $fix = '';
}

class DUP_PRO_Zip_U
{


    private static function getPossibleZipPaths()
    {
        return array(
            '/usr/bin/zip',
            '/opt/local/bin/zip', // RSR TODO put back in when we support shellexec on windows,
            //'C:/Program\ Files\ (x86)/GnuWin32/bin/zip.exe');
            '/opt/bin/zip',
            '/bin/zip',
            '/usr/local/bin/zip',
            '/usr/sfw/bin/zip',
            '/usr/xdg4/bin/zip',
        );
    }

    /**
     * Gets an array of possible ShellExec Zip problems on the server
     *
     * @return array Returns array of DUP_PRO_Problem_Fix objects
     */
    public static function getShellExecZipProblems()
    {
        $problem_fixes = array();
        if (!self::getShellExecZipPath()) {
            $filepath = null;
            $possible_paths = self::getPossibleZipPaths();
            foreach ($possible_paths as $path) {
                if (file_exists($path)) {
                    $filepath = $path;
                    break;
                }
            }

            if ($filepath == null) {
                $problem_fix          = new DUP_PRO_Problem_Fix();
                $problem_fix->problem = DUP_PRO_U::__('Zip executable not present');
                $problem_fix->fix     = DUP_PRO_U::__('Install the zip executable and make it accessible to PHP.');
                $problem_fixes[] = $problem_fix;
            }

            $cmds = array('shell_exec', 'escapeshellarg', 'escapeshellcmd', 'extension_loaded');
        //Function disabled at server level
            if (array_intersect($cmds, array_map('trim', explode(',', @ini_get('disable_functions'))))) {
                $problem_fix = new DUP_PRO_Problem_Fix();
                $problem_fix->problem = DUP_PRO_U::__('Required functions disabled in the php.ini.');
                $problem_fix->fix     = DUP_PRO_U::__('Remove any of the following from the disable_functions setting in the php.ini files: shell_exec, escapeshellarg, escapeshellcmd, and extension_loaded.');
                $problem_fixes[] = $problem_fix;
            }

            if (extension_loaded('suhosin')) {
                $suhosin_ini = @ini_get("suhosin.executor.func.blacklist");
                if (array_intersect($cmds, array_map('trim', explode(',', $suhosin_ini)))) {
                    $problem_fix = new DUP_PRO_Problem_Fix();
                    $problem_fix->problem = DUP_PRO_U::__('Suhosin is blocking PHP shell_exec.');
                    $problem_fix->fix     = DUP_PRO_U::__('In the php.ini file - Remove the following from the suhosin.executor.func.blacklist setting: shell_exec, escapeshellarg, escapeshellcmd, and extension_loaded.');
                    $problem_fixes[] = $problem_fix;
                }
            }
        }

        return $problem_fixes;
    }

    /**
     * Get the path to the zip program executable on the server
     *
     * @return string   Returns the path to the zip program
     */
    public static function getShellExecZipPath()
    {
        $filepath = null;
        if (DUP_PRO_Shell_U::isShellExecEnabled()) {
            if (shell_exec('hash zip 2>&1') == null) {
                $filepath = 'zip';
            } else {
                $possible_paths = self::getPossibleZipPaths();
                foreach ($possible_paths as $path) {
                    if (file_exists($path)) {
                        $filepath = $path;
                        break;
                    }
                }
            }
        }

        return $filepath;
    }

    public static function customShellArgEscapeSequence($arg)
    {
        return str_replace(array(' ', '-'), array('\ ', '\-'), $arg);
    }
}
