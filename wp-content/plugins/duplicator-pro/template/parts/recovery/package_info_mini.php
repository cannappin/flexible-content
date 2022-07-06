<?php

/**
 * Duplicator package row in table packages list
 *
 * @package Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 *
 */

defined("ABSPATH") or die("");

/**
 * Variables
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array $tplData
 */

/** @var \DUP_PRO_Package */
$package = $tplData['package'];
?>
<table>
    <tr>
        <td><b><?php esc_html_e('Package', 'duplicator-pro'); ?>:</b></td>
        <td><?php echo esc_html($package->Name); ?></td>
    </tr>
    <tr>
        <td><b><?php esc_html_e('Created', 'duplicator-pro'); ?>:</b>&nbsp; </td>
        <td>
            <?php
                echo $package->Created;
                $hours = $package->getPackageLife();
                echo '<i>&nbsp;-&nbsp;';
                printf(_n('%d hour ago', '%d hours ago', $hours, 'duplicator-pro'), $hours);
                echo '</i>';
            ?>
        </td>
    </tr>
</table>