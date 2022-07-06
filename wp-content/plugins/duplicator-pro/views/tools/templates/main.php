<?php

defined("ABSPATH") or die("");

use Duplicator\Controllers\ToolsPageController;
use Duplicator\Core\Controllers\ControllersManager;

$templates_tab_url = ControllersManager::getMenuLink(
    ControllersManager::TOOLS_SUBMENU_SLUG,
    ToolsPageController::L2_SLUG_TEMPLATE
);
$edit_template_url =  ControllersManager::getMenuLink(
    ControllersManager::TOOLS_SUBMENU_SLUG,
    ToolsPageController::L2_SLUG_TEMPLATE,
    null,
    array(
        'inner_page' => 'edit'
    )
);

$inner_page = isset($_REQUEST['inner_page']) ? sanitize_text_field($_REQUEST['inner_page']) : 'templates';

switch ($inner_page) {
    case 'templates':
        include(DUPLICATOR____PATH . '/views/tools/templates/template.list.php');
        break;
    case 'edit':
        include(DUPLICATOR____PATH . '/views/tools/templates/template.edit.php');
        break;
}
