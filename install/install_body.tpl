<html lang="{PHP_CurrentLangISOCode}">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <title>{PageTitle}</title>
        <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon" />
        <link rel="icon" href="../favicon.ico" type="image/x-icon" />
        <link rel="stylesheet" type="text/css" href="../skins/epicblue/default.css" />
        <link rel="stylesheet" type="text/css" href="../skins/epicblue/formate.css" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    </head>
    <body>
        <style>
        .main_container {
            width: 100%;
            text-align: center;
        }

        .content {
            display: inline-block;
        }

        th, td {
            padding: 3px !important;
        }

        td.c {
            text-align: center !important;
            color: orange;
        }

        th:nth-child(1):not(.info) {
            text-align: right !important;
        }

        th:nth-child(3):not(.info), .infobox {
            text-align: left !important;
        }

        .center {
            text-align: center !important;
        }

        input[type="text"], select {
            width: 90%;
            padding: 3px !important;
        }

        a {
            color: skyblue !important;
        }

        .redBorder {
            border-color: red;
        }
        </style>

        <div class="main_container">
            <div class="content">
                <form
                    action=""
                    method="post"
                    onsubmit="return confirm('{Form_Confirm}');"
                >
                    <input type="hidden" name="install" value="1"/>

                    <h1 style="font-size: 25pt;">{PageTitle}</h1>
                    <table style="width: 800px;">
                        <tbody style="{PHP_HideInfoBox}">
                            <tr>
                                <th style="color: {PHP_InfoBox_Color};" colspan="3" class="info infobox {PHP_InfoBox_Center}">{PHP_InfoBox_Text}</th>
                            </tr>
                            <tr style="visibility: hidden;">
                                <th>&nbsp;</th>
                            </tr>
                        </tbody>
                        <tbody style="{PHP_HideFormBox}">
                            <tr>
                                <th colspan="3" class="info">{Install_Info}</th>
                            </tr>


                            <tr>
                                <td class="c" colspan="3">{Header_DBConfig}</td>
                            </tr>
                            <tr>
                                <th style="width: 150px;">{Label_DBHost} <b style="color: orange">(*)</b></th>
                                <th style="width: 150px;"><input type="text" name="set_dbconfig_host" value="{set_dbconfig_host}" {PHP_BadVal_set_dbconfig_host} tabindex="1"/></th>
                                <th style="width: 450px;">{Tip_DBHost}</th>
                            </tr>
                            <tr>
                                <th>{Label_DBUser} <b style="color: orange">(*)</b></th>
                                <th><input type="text" name="set_dbconfig_user" value="{set_dbconfig_user}" {PHP_BadVal_set_dbconfig_user} tabindex="2"/></th>
                                <th>{Tip_DBUser}</th>
                            </tr>
                            <tr>
                                <th>{Label_DBPass} <b style="color: orange">(*)</b></th>
                                <th><input type="text" name="set_dbconfig_pass" value="{set_dbconfig_pass}" {PHP_BadVal_set_dbconfig_pass} tabindex="3"/></th>
                                <th>{Tip_DBPass}</th>
                            </tr>
                            <tr>
                                <th>{Label_DBName} <b style="color: orange">(*)</b></th>
                                <th><input type="text" name="set_dbconfig_name" value="{set_dbconfig_name}" {PHP_BadVal_set_dbconfig_name} tabindex="4"/></th>
                                <th>{Tip_DBName}</th>
                            </tr>
                            <tr>
                                <th>{Label_DBPrefix}</th>
                                <th><input type="text" name="set_dbconfig_prefix" value="{set_dbconfig_prefix}" {PHP_BadVal_set_dbconfig_prefix} tabindex="5"/></th>
                                <th>{Tip_DBPrefix}</th>
                            </tr>


                            <tr>
                                <td class="c" colspan="3">{Header_Constants}</td>
                            </tr>
                            <tr>
                                <th>{Label_Const_UniID}</th>
                                <th><input type="text" name="set_const_uniid" value="{set_const_uniid}" {PHP_BadVal_set_const_uniid} tabindex="6"/></th>
                                <th>{Tip_Const_UniID}</th>
                            </tr>
                            <tr>
                                <th>{Label_Const_Domain} <b style="color: orange">(*)</b></th>
                                <th><input type="text" name="set_const_domain" value="{set_const_domain}" {PHP_BadVal_set_const_domain} tabindex="7"/></th>
                                <th>{Tip_Const_Domain}</th>
                            </tr>
                            <tr>
                                <th>{Label_Const_Subdomain}</th>
                                <th><input type="text" name="set_const_subdomain" value="{set_const_subdomain}" tabindex="8"/></th>
                                <th>{Tip_Const_Subdomain}</th>
                            </tr>
                            <tr>
                                <th>{Label_Const_ReCaptcha_Enable}</th>
                                <th><input type="checkbox" name="set_const_recaptcha_enable" {set_const_recaptcha_enable} tabindex="9"/></th>
                                <th>{Tip_Const_ReCaptcha_Enable}</th>
                            </tr>
                            <tr>
                                <th>{Label_Const_ReCaptcha_ServerIP_as_Hostname}</th>
                                <th><input type="checkbox" name="set_const_recaptcha_serverip_as_hostname" {set_const_recaptcha_serverip_as_hostname} tabindex="10"/></th>
                                <th>{Tip_Const_ReCaptcha_ServerIP_as_Hostname}</th>
                            </tr>
                            <tr>
                                <th>{Label_Const_ReCaptcha_Public}</th>
                                <th><input type="text" name="set_const_recaptcha_public" value="{set_const_recaptcha_public}" {PHP_BadVal_set_const_recaptcha_public} tabindex="11"/></th>
                                <th>{Tip_Const_ReCaptcha_Public}</th>
                            </tr>
                            <tr>
                                <th>{Label_Const_ReCaptcha_Private}</th>
                                <th><input type="text" name="set_const_recaptcha_private" value="{set_const_recaptcha_private}" {PHP_BadVal_set_const_recaptcha_private} tabindex="12"/></th>
                                <th>{Tip_Const_ReCaptcha_Private}</th>
                            </tr>


                            <tr>
                                <td class="c" colspan="3">{Header_AdminUser}</td>
                            </tr>
                            <tr>
                                <th>{Label_Admin_Username} <b style="color: orange">(*)</b></th>
                                <th><input type="text" name="set_admin_username" value="{set_admin_username}" {PHP_BadVal_set_admin_username} tabindex="13"/></th>
                                <th>{Tip_Admin_Username}</th>
                            </tr>
                            <tr>
                                <th>{Label_Admin_Password} <b style="color: orange">(*)</b></th>
                                <th><input type="text" name="set_admin_password" value="{set_admin_password}" {PHP_BadVal_set_admin_password} tabindex="14"/></th>
                                <th>{Tip_Admin_Password}</th>
                            </tr>
                            <tr>
                                <th>{Label_Admin_Email} <b style="color: orange">(*)</b></th>
                                <th><input type="text" name="set_admin_email" value="{set_admin_email}" {PHP_BadVal_set_admin_email} tabindex="15"/></th>
                                <th>{Tip_Admin_Email}</th>
                            </tr>


                            <tr>
                                <td class="c" colspan="3">{Header_UniMain}</td>
                            </tr>
                            <tr>
                                <th>{Label_Uni_GameName} <b style="color: orange">(*)</b></th>
                                <th><input type="text" name="set_uni_gamename" value="{set_uni_gamename}" {PHP_BadVal_set_uni_gamename} tabindex="16"/></th>
                                <th>{Tip_Uni_GameName}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_GameDefaultLang} <b style="color: orange">(*)</b></th>
                                <th>
                                    <select
                                        name="set_uni_gamedefaultlang"
                                        tabindex="17"
                                        {PHP_BadVal_set_uni_gamedefaultlang}
                                    >
                                        {PHP_Dynamic_GameDefaultLang_options}
                                    </select>
                                </th>
                                <th>{Tip_Uni_GameDefaultLang}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_GameSpeed} <b style="color: orange">(*)</b></th>
                                <th><input type="text" name="set_uni_gamespeed" value="{set_uni_gamespeed}" {PHP_BadVal_set_uni_gamespeed} tabindex="18"/></th>
                                <th>{Tip_Uni_GameSpeed}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_FleetSpeed} <b style="color: orange">(*)</b></th>
                                <th><input type="text" name="set_uni_fleetspeed" value="{set_uni_fleetspeed}" {PHP_BadVal_set_uni_fleetspeed} tabindex="19"/></th>
                                <th>{Tip_Uni_FleetSpeed}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_ResourceSpeed} <b style="color: orange">(*)</b></th>
                                <th><input type="text" name="set_uni_resourcespeed" value="{set_uni_resourcespeed}" {PHP_BadVal_set_uni_resourcespeed} tabindex="20"/></th>
                                <th>{Tip_Uni_ResourceSpeed}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_MotherFields} <b style="color: orange">(*)</b></th>
                                <th><input type="text" name="set_uni_motherfields" value="{set_uni_motherfields}" {PHP_BadVal_set_uni_motherfields} tabindex="21"/></th>
                                <th>{Tip_Uni_MotherFields}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_FleetDebris} <b style="color: orange">(*)</b></th>
                                <th><input type="text" name="set_uni_fleetdebris" value="{set_uni_fleetdebris}" {PHP_BadVal_set_uni_fleetdebris} tabindex="22"/></th>
                                <th>{Tip_Uni_FleetDebris}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_DefenseDebris} <b style="color: orange">(*)</b></th>
                                <th><input type="text" name="set_uni_defensedebris" value="{set_uni_defensedebris}" {PHP_BadVal_set_uni_defensedebris} tabindex="23"/></th>
                                <th>{Tip_Uni_DefenseDebris}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_MissileDebris} <b style="color: orange">(*)</b></th>
                                <th><input type="text" name="set_uni_missiledebris" value="{set_uni_missiledebris}" {PHP_BadVal_set_uni_missiledebris} tabindex="24"/></th>
                                <th>{Tip_Uni_MissileDebris}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_MailActivationNeeded}</th>
                                <th><input type="checkbox" name="set_uni_mailactivationneeded" {set_uni_mailactivationneeded} tabindex="25"/></th>
                                <th>{Tip_Uni_MailActivationNeeded}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_TelemetryEnable}</th>
                                <th><input type="checkbox" name="set_uni_telemetryenable" {set_uni_telemetryenable} tabindex="26"/></th>
                                <th>{Tip_Uni_TelemetryEnable}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_AutoToolPass_StatBuilder}</th>
                                <th><input type="text" name="set_uni_autotoolpass_statbuilder" value="{set_uni_autotoolpass_statbuilder}" tabindex="27"/></th>
                                <th>{Tip_Uni_AutoToolPass_StatBuilder}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_AutoToolPass_GarbageCollector}</th>
                                <th><input type="text" name="set_uni_autotoolpass_gc" value="{set_uni_autotoolpass_gc}" tabindex="28"/></th>
                                <th>{Tip_Uni_AutoToolPass_GarbageCollector}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_AutoToolPass_GZipLog}</th>
                                <th><input type="text" name="set_uni_autotoolpass_gziplog" value="{set_uni_autotoolpass_gziplog}" tabindex="29"/></th>
                                <th>{Tip_Uni_AutoToolPass_GZipLog}</th>
                            </tr>


                            <tr>
                                <td class="c" colspan="3">{Header_UniProtections}</td>
                            </tr>
                            <tr>
                                <th>{Label_Uni_NoobProtectionEnable}</th>
                                <th><input type="checkbox" name="set_uni_noobprt_enable" {set_uni_noobprt_enable} tabindex="30"/></th>
                                <th>{Tip_Uni_NoobProtectionEnable}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_NoobProtectionBasicTimeLimit}</th>
                                <th><input type="text" name="set_uni_noobprt_basictime" value="{set_uni_noobprt_basictime}" {PHP_BadVal_set_uni_noobprt_basictime} tabindex="31"/></th>
                                <th>{Tip_Uni_NoobProtectionBasicTimeLimit}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_NoobProtectionBasicLimitMultiplier}</th>
                                <th><input type="text" name="set_uni_noobprt_basicmultiplier" value="{set_uni_noobprt_basicmultiplier}" {PHP_BadVal_set_uni_noobprt_basicmultiplier} tabindex="32"/></th>
                                <th>{Tip_Uni_NoobProtectionBasicLimitMultiplier}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_NoobProtectionRemove}</th>
                                <th><input type="text" name="set_uni_noobprt_remove" value="{set_uni_noobprt_remove}" {PHP_BadVal_set_uni_noobprt_remove} tabindex="33"/></th>
                                <th>{Tip_Uni_NoobProtectionRemove}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_NoobProtectionIdleDays}</th>
                                <th><input type="text" name="set_uni_noobprt_idledays" value="{set_uni_noobprt_idledays}" {PHP_BadVal_set_uni_noobprt_idledays} tabindex="34"/></th>
                                <th>{Tip_Uni_NoobProtectionIdleDays}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_NoobProtectionFirstLogin}</th>
                                <th><input type="text" name="set_uni_noobprt_firstlogin" value="{set_uni_noobprt_firstlogin}" {PHP_BadVal_set_uni_noobprt_firstlogin} tabindex="35"/></th>
                                <th>{Tip_Uni_NoobProtectionFirstLogin}</th>
                            </tr>

                            <tr>
                                <td class="c" colspan="3">{Header_UniAntiAbuse}</td>
                            </tr>
                            <tr>
                                <th>{Label_Uni_AntiFarm_Enabled}</th>
                                <th><input type="checkbox" name="set_uni_antifarmenable" {set_uni_antifarmenable} tabindex="36"/></th>
                                <th>{Tip_Uni_AntiFarm_Enabled}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_AntiFarm_Ratio}</th>
                                <th><input type="text" name="set_uni_antifarmratio" value="{set_uni_antifarmratio}" {PHP_BadVal_set_uni_antifarmratio} tabindex="37"/></th>
                                <th>{Tip_Uni_AntiFarm_Ratio}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_AntiFarm_TotalCount}</th>
                                <th><input type="text" name="set_uni_antifarmtotalcount" value="{set_uni_antifarmtotalcount}" {PHP_BadVal_set_uni_antifarmtotalcount} tabindex="38"/></th>
                                <th>{Tip_Uni_AntiFarm_TotalCount}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_AntiFarm_PlanetCount}</th>
                                <th><input type="text" name="set_uni_antifarmplanetcount" value="{set_uni_antifarmplanetcount}" {PHP_BadVal_set_uni_antifarmplanetcount} tabindex="39"/></th>
                                <th>{Tip_Uni_AntiFarm_PlanetCount}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_AntiBash_Enabled}</th>
                                <th><input type="checkbox" name="set_uni_antibashenable" {set_uni_antibashenable} tabindex="40"/></th>
                                <th>{Tip_Uni_AntiBash_Enabled}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_AntiBash_Interval}</th>
                                <th><input type="text" name="set_uni_antibashinterval" value="{set_uni_antibashinterval}" {PHP_BadVal_set_uni_antibashinterval} tabindex="41"/></th>
                                <th>{Tip_Uni_AntiBash_Interval}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_AntiBash_TotalCount}</th>
                                <th><input type="text" name="set_uni_antibashtotalcount" value="{set_uni_antibashtotalcount}" {PHP_BadVal_set_uni_antibashtotalcount} tabindex="42"/></th>
                                <th>{Tip_Uni_AntiBash_TotalCount}</th>
                            </tr>
                            <tr>
                                <th>{Label_Uni_AntiBash_PlanetCount}</th>
                                <th><input type="text" name="set_uni_antibashplanetcount" value="{set_uni_antibashplanetcount}" {PHP_BadVal_set_uni_antibashplanetcount} tabindex="43"/></th>
                                <th>{Tip_Uni_AntiBash_PlanetCount}</th>
                            </tr>

                            <tr>
                                <td class="c" colspan="3"><input tabindex="44" type="submit" value="{Button_Install}" style="width: 90%; font-weight: bold; padding: 3px !important; color: lime;"/></td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </body>
</html>
