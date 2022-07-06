<?php

/**
 * Restore only package
 *
 * Standard: PSR-2
 *
 * @package SC\DUPX\
 * @link http://www.php-fig.org/psr/psr-2/
 *
 */

use Duplicator\Libs\Snap\SnapWP;

/**
 * Class that manages installations where it is not possible to migrate packages but only restore backups
 */
class DUP_PRO_RestoreOnly_Package
{

    /** @var self */
    protected static $instance = null;

    /**
     * Class contructor
     */
    private function __construct()
    {
    }

    /**
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Init
     *
     * @return void
     */
    public function init()
    {
        if (!self::canBeMigrate()) {
            add_filter('duplicator_pro_overwrite_params_data', array(__CLASS__, 'forceSkipReplace'));
        }
    }

    /**
     * Returns true if the package is restore only
     *
     * @return bool
     */
    public static function isRestoreOnly()
    {
        $overwriteInstallerParams = apply_filters('duplicator_pro_overwrite_params_data', array());
        return (
            isset($overwriteInstallerParams['mode_chunking']['value']) &&
            $overwriteInstallerParams['mode_chunking']['value'] == 3 &&
            isset($overwriteInstallerParams['mode_chunking']['formStatus']) &&
            $overwriteInstallerParams['mode_chunking']['formStatus'] == 'st_infoonly'
            );
    }

    /**
     * Import data so that the package cannot be migrated
     *
     * @param array $data importa params data
     * 
     * @return array
     */
    public static function forceSkipReplace($data)
    {
        $data['mode_chunking'] = array(
            'value'      => 3,
            'formStatus' => 'st_infoonly'
        );
        return $data;
    }

    /**
     * If false the current site cannot be migrated
     *
     * @return bool
     */
    private static function canBeMigrate()
    {
        $homePath = trailingslashit(SnapWP::getHomePath());
        $canBeMigrated = (strlen($homePath) > 3);
        return apply_filters('duplicator_pro_package_can_be_migrate', $canBeMigrated);
    }
}
