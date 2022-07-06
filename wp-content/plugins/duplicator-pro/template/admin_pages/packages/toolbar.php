<?php

/**
 * Duplicator package row in table packages list
 *
 * @package Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 *
 */

use Duplicator\Controllers\ImportPageController;
use Duplicator\Controllers\PackagesPageController;
use Duplicator\Controllers\SettingsPageController;
use Duplicator\Controllers\ToolsPageController;

defined("ABSPATH") or die("");

/**
 * Variables
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng $tplMng
 * @var array $tplData
 */

$global                 = DUP_PRO_Global_Entity::get_instance();
$showGift               = (
    defined('DUPLICATOR_PRO_GIFT_THIS_RELEASE')
    && DUPLICATOR_PRO_GIFT_THIS_RELEASE === true
    && !$global->dupHidePackagesGiftFeatures
);
$active_package_present = DUP_PRO_Package::is_active_package_present();

$settingsUrl = esc_url($ctrlMng->getMenuLink($ctrlMng::SETTINGS_SUBMENU_SLUG, SettingsPageController::L2_SLUG_PACKAGE));
$templateUrl = esc_url($ctrlMng->getMenuLink($ctrlMng::TOOLS_SUBMENU_SLUG, ToolsPageController::L2_SLUG_TEMPLATE));
$recoveryUrl = esc_url($ctrlMng->getMenuLink($ctrlMng::TOOLS_SUBMENU_SLUG, ToolsPageController::L2_SLUG_RECOVERY));
?>
<input type="hidden" id="action" name="action" />
<input type="hidden" id="action-parameter" name="action-parameter" />

<!-- ====================
TOOL-BAR -->
<table class="dpro-edit-toolbar">
<tr>
    <td>
        <label for="dup-pack-bulk-actions" class="screen-reader-text">Select bulk action</label>
        <select id="dup-pack-bulk-actions">
            <option value="-1" selected="selected">
                <?php DUP_PRO_U::esc_html_e("Bulk Actions") ?>
            </option>
            <option value="delete" title="<?php DUP_PRO_U::esc_attr_e("Delete selected package(s)") ?>">
                <?php DUP_PRO_U::esc_html_e("Delete") ?>
            </option>
        </select>
        <input type="button" id="dup-pack-bulk-apply" class="button action"
            value="<?php DUP_PRO_U::esc_attr_e("Apply") ?>"
            onclick="DupPro.Pack.ConfirmDelete()" />

        <span class="btn-separator"></span>

        <span class="button grey-icon dup-contextual-help"
            title="<?php DUP_PRO_U::esc_attr_e("Tutorial") ?>"
            onclick="DupPro.Pack.openLinkDetails()">
            <i class="far fa-lightbulb fa-fw"></i>
        </span>

        <a href="<?php echo $settingsUrl; ?>"
           class="button grey-icon dpro-toolbar-settings"
           title="<?php DUP_PRO_U::esc_attr_e("Package Settings") ?>" >
            <i class="fas fa-sliders-h fa-fw"></i>
        </a>
        <a href="<?php echo $templateUrl; ?>" 
           class="button dpro-toolbar-templates"
           title="<?php DUP_PRO_U::esc_attr_e("Templates") ?>" >
            <i class="far fa-clone fa-fw"></i>
        </a>

        <span class="btn-separator"></span>
        <a href="<?php echo ImportPageController::getImportPageLink(); ?>" 
           id="btn-logs-dialog"
           class="button dpro-toolbar-import" 
           title="<?php DUP_PRO_U::esc_attr_e("Import") ?>">
           <i class="fas fa-arrow-alt-circle-down fa-fw"></i>
        </a>

        <span 
            class="dpro-toolbar-recovery-info button <?php echo (DUP_PRO_Package_Recover::getRecoverPackageId() === false ? 'dup-recovery-unset' : ''); ?>" 
            title="<?php DUP_PRO_U::esc_attr_e("Recovery") ?>">
            <i class="fas fa-undo-alt fa-fw"></i>
        </span>

        <?php if ($showGift) { ?>
            <a href="javascript:void(0);" class="button dup-data-badge"  id="btn-logs-gift" 
               title="<?php DUP_PRO_U::esc_attr_e("New Features!") ?>"
               data-badge="2">
                <i class="fa fa-gift fa-fw"></i>
                <?php //DUP_PRO_U::esc_html_e("New!") ?>
            </a>
        <?php } ?>
    </td>
    <td>
        <div class="btnnav">
            <a href="<?php echo esc_url(PackagesPageController::getInstance()->getPackageBuildUrl()); ?>"
               id="dup-pro-create-new" 
               onClick="return DupPro.Pack.CreateNew(this);" 
               class="button <?php echo ($active_package_present ? 'disabled' : ''); ?>">
               <?php DUP_PRO_U::esc_html_e('Create New'); ?>
            </a>
        </div>
    </td>
</tr>
</table>
