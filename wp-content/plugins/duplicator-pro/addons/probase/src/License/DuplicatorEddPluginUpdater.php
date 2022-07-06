<?php

/**
 * @package Duplicator
 */

namespace Duplicator\Addons\ProBase\License;

use Duplicator\Addons\ProBase\Vendor\EDD\EDD_SL_Plugin_Updater;
use Duplicator\Libs\Snap\TraitAccessPrivate;

/**
 * Allows plugins to use their own update API.
 */
class DuplicatorEddPluginUpdater extends EDD_SL_Plugin_Updater
{
    use TraitAccessPrivate;

    /**
     * Class contructor
     */
    public function __construct()
    {
        // Fill lists from trait AccessPrivate
        self::$allowedPrivateMethodsCallList       = array("get_cache_key");
        self::$allowedPrivateStaticMethodsCallList = array();
        self::$allowedPrivateAttributesGetList     = array("slug", "api_data", "beta");
        self::$allowedPrivateAttributesSetList     = array();
        self::$allowedForAll                       = false;

        $args = func_get_args();
        call_user_func_array(array('parent', '__construct'), $args);
    }

    /**
     * This method is overriden here, because we need to change cache duration from 3 to 12 hours!
     * Adds the plugin version information to the database.
     *
     * @param string $value     cache value
     * @param string $cache_key cache key
     *
     * @return void
     */
    public function set_version_info_cache($value = '', $cache_key = '') // phpcs:ignore
    {

        if (empty($cache_key)) {
            $cache_key = $this->get_cache_key();
        }

        $data = array(
            'timeout' => strtotime('+12 hours', time()),
            'value'   => json_encode($value),
        );

        update_option($cache_key, $data, 'no');

        // Delete the duplicate option
        delete_option('edd_api_request_' . md5(serialize($this->slug . $this->api_data['license'] . $this->beta)));
    }

    /**
     * Clears the version cache and forces plugin version check
     *
     * @uses set_site_transient()
     *
     * @return void
     */
    public function clear_version_cache() // phpcs:ignore
    {
        $this->set_version_info_cache(false);
    }
}
