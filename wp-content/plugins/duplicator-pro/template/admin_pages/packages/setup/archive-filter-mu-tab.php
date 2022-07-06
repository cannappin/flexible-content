<?php

/**
 * @package Duplicator
 */

use Duplicator\Addons\ProBase\License\License;

defined("ABSPATH") or die("");

/**
 * Variables
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array $tplData
 */

?>
<div class="filter-mu-tab-content" >
    <div style="<?php echo $multisite_css ?>; max-width:900px">
    <?php
        $license = License::getType();

        echo '<b>' . DUP_PRO_U::esc_html__("Overview:") . '</b><br/>';
        $txt_mu_license = DUP_PRO_U::__("This Duplicator Pro <a href='admin.php?page=duplicator-pro-settings&tab=licensing' target='lic'>%s</a> has "
            . "Multisite Basic capability, ");
        $txt_mu_basic   = DUP_PRO_U::__(
            "which backs up and migrates an entire multisite network. "
            . "Subsite to standalone conversion is not supported with Multisite Basic, only with Multisite Plus+.<br/><br/>"
            . "To gain access to Multisite Plus+ please login to your dashboard and upgrade to either a "
            . "<a href='https://snapcreek.com/dashboard/' target='snap'>Business or Gold License</a>."
        );

        switch ($license) {
            case License::TYPE_PERSONAL:
                printf(wp_kses($txt_mu_license, array('a' => array())), DUP_PRO_U::esc_html__("Personal License"));
                echo $txt_mu_basic;
                break;
            case License::TYPE_FREELANCER:
                printf(wp_kses($txt_mu_license, array('a' => array())), DUP_PRO_U::esc_html__("Freelancer License"));
                echo $txt_mu_basic;
                break;
            case License::TYPE_BUSINESS_GOLD:
                DUP_PRO_U::esc_html_e(
                    "When you want to move a full multisite network or convert a subsite to a standalone site just "
                    . "create a standard package like you would with a single site. "
                    . "Then browse to the installer and choose either 'Restore entire multisite network'  or 'Convert subsite into a standalone site'.  "
                    . "These options will be present on Step 1 of the installer when restoring a Multisite package."
                );

                echo '<br/><br/>';
                echo wp_kses(
                    DUP_PRO_U::__(
                        "<u><b>Important:</b></u> Full network restoration is an installer option only if you include <b>all</b> subsites."
                        . " If any subsites are filtered then you may only restore individual subsites as standalones sites at install-time."
                    ),
                    array(
                        'b' => array(),
                        'u' => array(),
                    )
                );
                break;

            default:
                printf($txt_mu_license, DUP_PRO_U::__("Unlicensed"));
                echo $txt_mu_basic;
        }
        ?>
    </div>

    <?php if (is_multisite() && License::isBusiness()) :?>
        <table class="mu-opts">
            <tr>
                <td>
                    <b><?php DUP_PRO_U::esc_html_e("Included Sub-Sites"); ?>:</b><br/>
                    <select name="mu-include[]" id="mu-include" multiple="true" class="mu-selector">
                        <?php
                        foreach (DUP_PRO_MU::getSubsites() as $site) {
                            echo "<option value='" . esc_attr($site->id) . "'>" . esc_html($site->domain . $site->path) . "</option>";
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <button type="button" id="mu-exclude-btn" class="mu-push-btn"><i class="fa fa-chevron-right"></i></button><br/>
                    <button type="button" id="mu-include-btn" class="mu-push-btn"><i class="fa fa-chevron-left"></i></button>
                </td>
                <td>
                    <b><?php DUP_PRO_U::esc_html_e("Excluded Sub-Sites"); ?>:</b><br/>
                    <select name="mu-exclude[]" id="mu-exclude" multiple="true" class="mu-selector"></select>
                </td>
            </tr>
        </table>

        <div class="dpro-panel-optional-txt" style="text-align: left">
            <?php DUP_PRO_U::esc_html_e(
                "This section allows you to control which sub-sites of a multisite network you want to include within your package. " .
                "The 'Included Sub-Sites' will also be available to choose from at install time."
            ); ?> <br/>
            <?php DUP_PRO_U::esc_html_e(
                "By default all packages are include. "
                . "The ability to exclude sub-sites are intended to help shrink your package if needed."
            ); ?>
        </div>
    <?php endif; ?>
</div>