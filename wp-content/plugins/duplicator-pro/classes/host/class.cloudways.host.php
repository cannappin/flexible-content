<?php

/**
 * cloudways custom hosting class
 *
 * Standard: PSR-2
 *
 * @package SC\DUPX\HOST
 * @link http://www.php-fig.org/psr/psr-2/
 *
 */

use Duplicator\Libs\Snap\SnapUtil;

class DUP_PRO_Cloudways_Host implements DUP_PRO_Host_interface
{

    public static function getIdentifier()
    {
        return DUP_PRO_Custom_Host_Manager::HOST_CLOUDWAYS;
    }

    public function isHosting()
    {
        ob_start();
        SnapUtil::phpinfo();
        $serverinfo = ob_get_clean();
        return (strpos($serverinfo, "cloudwaysapps") !== false);
    }

    public function init()
    {
        add_filter('duplicator_pro_installer_file_path', array(__CLASS__, 'installerFilePath'), 10, 1);
    }

    public static function installerFilePath($path)
    {
        $path_info = pathinfo($path);
        $newPath   = $path;
        if ('php' == $path_info['extension']) {
            $newPath = substr_replace($path, '.txt', -4);
        }
        return $newPath;
    }
}
