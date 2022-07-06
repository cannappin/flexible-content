<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<tr>
    <td class="col-opt">Safe Mode</td>
    <td>
        Safe mode is designed to configure the site with specific options at install time to help overcome issues that may happen during the install
        were the site is having issues. These options should only be used if you run into issues after you have tried to run an install.
        <br/><br/>

        <b>Disabled:</b><br/>
        This is the default.   This option will not apply any additional settings at install time.
        <br/><br/>

        <b>Enabled:</b><br/>
        When enabled the safe mode option will disable all the plugins at install time, except for the Duplicator Pro plugin.
        <i>Note:  When this option is set you will need to manually re-enable the plugins that need to be enabled after the install from the
        WordPress admin plugins page.</i>
        <br/><br/>
    </td>
</tr>