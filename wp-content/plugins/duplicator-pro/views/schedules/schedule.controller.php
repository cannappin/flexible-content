<?php

defined("ABSPATH") or die("");

use Duplicator\Controllers\SchedulePageController;
use Duplicator\Core\Controllers\ControllersManager;

DUP_PRO_U::hasCapability('export');

$inner_page = isset($_REQUEST['inner_page']) ? sanitize_text_field($_REQUEST['inner_page']) : 'schedules';
/*
switch ($inner_page)
{
    case 'edit':
        if (!wp_verify_nonce($_GET['_wpnonce'], 'edit-schedule')) {
            die('Security issue');
        }
        break;
}*/

$schedules_tab_url = ControllersManager::getMenuLink(
    ControllersManager::SCHEDULES_SUBMENU_SLUG,
    SchedulePageController::L2_SLUG_MAIN_SCHEDULES
);
$edit_schedule_url = ControllersManager::getMenuLink(
    ControllersManager::SCHEDULES_SUBMENU_SLUG,
    SchedulePageController::L2_SLUG_MAIN_SCHEDULES,
    null,
    array(
        'inner_page' => 'edit',
        '_wpnonce'   => wp_create_nonce('edit-schedule')
    )
);

switch ($inner_page) {
    case 'schedules':
        include(DUPLICATOR____PATH . '/views/schedules/schedule.list.php');
        break;
    case 'edit':
        include(DUPLICATOR____PATH . '/views/schedules/schedule.edit.php');
        break;
}
