<script src="../libs/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="../libs/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js" type="text/javascript"></script>
<script src="../libs/jquery-ui-datepicker-{JS_DatePicker_TranslationLang}/jquery-ui-datepicker-{JS_DatePicker_TranslationLang}.min.js" type="text/javascript"></script>
<script>
var JSLang = {'JS_ConfirmNeeded': '{JS_ConfirmNeeded}'};
</script>
<script src="../dist/js/admin/settings_body.cachebuster-1561455380555.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="../dist/css/admin/settings_body.cachebuster-1546564327123.min.css" />
<link rel="stylesheet" type="text/css" href="../libs/jquery-ui/jquery-ui.min.css" />
<br />
<form action="settings.php" method="post" id="thisForm">
    <input type="hidden" name="opt_save" value="1" />
    <table width="800">
        <tbody {Msg_Hide}>
            <tr>
                <th colspan="3" class="pad5 {Msg_Color}">{Msg_Text}</th>
            </tr>
            <tr>
                <th colspan="3" class="smallLine inv">&nbsp;</th>
            </tr>
        </tbody>
        <tr>
            <td class="c" colspan="3">{Body_Title}</td>
        </tr>
        <tr>
            <td class="c tRight tdLabel">{Headers_EnforceConfigReload}</td>
            <th class="pad5" colspan="2">
                <input type="button" id="ConfigReload" style="color: red; width: 80%; padding: 3px; font-weight: 700;" value="{Headers_EnforceConfigReload_Button}"/>
            </th>
        </tr>
        <tr>
            <th colspan="3" class="smallLine">&nbsp;</th>
        </tr>
        <tr>
            <td class="c tRight tdLabel">{Headers_EngineVersion}</td>
            <th class="pad5"><input type="text" class="pad2" name="EngineInfo_Version" value="{PHP_EngineInfo_Version}"/></th>
            <th class="thDefault">(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_EngineBuild}</td>
            <th class="pad5"><input type="text" class="pad2" name="EngineInfo_BuildNo" value="{PHP_EngineInfo_BuildNo}"/></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <th colspan="3" class="smallLine">&nbsp;</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_CloseServer}</td>
            <th class="pad5"><input type="checkbox" name="game_disable" {PHP_game_disable}/></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_CloseServerReason}</td>
            <th class="pad5"><textarea name="close_reason" class="pad2">{PHP_close_reason}</textarea></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <th colspan="3" class="smallLine">&nbsp;</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_EnforceRulesAccept}</td>
            <th class="pad5"><input type="checkbox" name="enforceRulesAcceptance" {PHP_enforceRulesAcceptance}/></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_EnforceRulesLastChange}</td>
            <th class="pad5"><input type="text" class="pad2" name="last_rules_changes" value="{PHP_last_rules_changes}"/></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <th colspan="3" class="smallLine">&nbsp;</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_Telemetry}</td>
            <th class="pad5"><input type="checkbox" name="TelemetryEnabled" {PHP_TelemetryEnabled}/></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_BBCode}</td>
            <th class="pad5"><input type="checkbox" name="enable_bbcode" {PHP_enable_bbcode}/></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <th colspan="3" class="smallLine">&nbsp;</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_OverviewAdminInfoEnabled}</td>
            <th class="pad5"><input type="checkbox" name="OverviewNewsFrame" {PHP_OverviewNewsFrame}/></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_OverviewAdminInfoText}</td>
            <th class="pad5"><textarea name="OverviewNewsText" class="pad2">{PHP_OverviewNewsText}</textarea></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <th colspan="3" class="smallLine">&nbsp;</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_OverviewBottomBannersEnabled}</td>
            <th class="pad5"><input type="checkbox" name="OverviewBanner" {PHP_OverviewBanner}/></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_OverviewBottomBannersText}</td>
            <th class="pad5"><textarea name="OverviewClickBanner" class="pad2">{PHP_OverviewClickBanner}</textarea></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <th colspan="3" class="smallLine">&nbsp;</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_BannedMailDomains}</td>
            <th class="pad5"><textarea name="BannedMailDomains" class="pad2 h80px">{PHP_BannedMailDomains}</textarea></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <th colspan="3" class="smallLine">&nbsp;</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_BannedIPs}</td>
            <th class="pad5"><textarea name="banned_ip_list" class="pad2 h80px">{PHP_banned_ip_list}</textarea></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <th colspan="3" class="smallLine">&nbsp;</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_Game_GameSpeed}</td>
            <th class="pad5"><input type="text" name="game_speed" class="needConfirm pad2" value="{PHP_game_speed}"/>x</th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_Game_ResourceSpeed}</td>
            <th class="pad5"><input type="text" name="resource_multiplier" class="needConfirm pad2" value="{PHP_resource_multiplier}"/>x</th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_Game_FleetSpeed}</td>
            <th class="pad5"><input type="text" name="fleet_speed" class="needConfirm pad2" value="{PHP_fleet_speed}"/>x</th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_Game_DebrisFleet}</td>
            <th class="pad5"><input type="text" name="Fleet_Cdr" class="needConfirm pad2" value="{PHP_Fleet_Cdr}"/>%</th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_Game_DebrisDefence}</td>
            <th class="pad5"><input type="text" name="Defs_Cdr" class="needConfirm pad2" value="{PHP_Defs_Cdr}"/>%</th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_Game_DebrisDefenceMissile}</td>
            <th class="pad5"><input type="text" name="Debris_Def_Rocket" class="needConfirm pad2" value="{PHP_Debris_Def_Rocket}"/>%</th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_Game_StatPoints}</td>
            <th class="pad5"><input type="text" name="stat_settings" class="needConfirm pad2" value="{PHP_stat_settings}"/>:1</th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_Game_InitialFields}</td>
            <th class="pad5"><input type="text" name="initial_fields" class="needConfirm pad2" value="{PHP_initial_fields}"/></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_Game_ResearchOnLabBuild}</td>
            <th class="pad5"><input type="checkbox" name="BuildLabWhileRun" class="needConfirm" {PHP_BuildLabWhileRun}/></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_Game_BasicIncome_Metal}</td>
            <th class="pad5"><input type="text" name="metal_basic_income" class="needConfirm pad2" value="{PHP_metal_basic_income}"/></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_Game_BasicIncome_Crystal}</td>
            <th class="pad5"><input type="text" name="crystal_basic_income" class="needConfirm pad2" value="{PHP_crystal_basic_income}"/></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_Game_BasicIncome_Deuterium}</td>
            <th class="pad5"><input type="text" name="deuterium_basic_income" class="needConfirm pad2" value="{PHP_deuterium_basic_income}"/></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <td class="c tRight">{Headers_Game_BasicIncome_Energy}</td>
            <th class="pad5"><input type="text" name="energy_basic_income" class="needConfirm pad2" value="{PHP_energy_basic_income}"/></th>
            <th>(<a class="doReset">{Labels_DefaultOption}</a>)</th>
        </tr>
        <tr>
            <th colspan="3" class="smallLine">&nbsp;</th>
        </tr>
        <tr>
            <th class="pad2" colspan="3"><input value="{Body_Save}" type="submit" style="width: 80%; padding: 3px; font-weight: 700;"/></th>
        </tr>
    </table>
</form>
