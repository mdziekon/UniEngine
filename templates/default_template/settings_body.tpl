<script>
var OldPass = '{MD5OldPass}';
var VacationMiliseconds = {PHP_Insert_VacationMinDuration} * 1000;
var OldResSort = '{OldResSort_ArrayString}';
var OverrideTab = '{SetActiveMarker}';
var TemplateData = {'SaveOnlyThis': '{SaveOnlyThis}', 'SaveAll': '{SaveAll}', 'use_skin_check': '{use_skin_check}', 'AYS_WantNoSkin': '{AYS_WantNoSkin}', 'skin_path': '{skin_path}', 'SetSkin_BadNetSkin': '{SetSkin_BadNetSkin}', 'SetSkin_BadLocSkin': '{SetSkin_BadLocSkin}', 'SetSkin_AjaxError': '{SetSkin_AjaxError}', 'IgnoreUserNow': '{IgnoreUserNow}', 'DeleteFromIgnoreList': '{DeleteFromIgnoreList}', 'atHour': '{atHour}'};
</script>
<script src="libs/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="libs/jquery-farbtastic/jquery.farbtastic-1.2.min.js" type="text/javascript"></script>
<script src="dist/js/settings.cachebuster-1546739003831.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="dist/css/settings.cachebuster-1546564327123.min.css" />
<link rel="stylesheet" type="text/css" href="libs/jquery-farbtastic/jquery.farbtastic-1.2.min.css" />
<br/>
<form action="" method="post" style="margin: 0px;">
<input type="hidden" name="save" value="yes"/>
<input type="hidden" name="saveType" value="{SaveAll}"/>
<div id="settings">
    <div id="settingsMenu">
        <table id="tableMenu">
            <tr>
                <td class="c center">{Menu_Title}</td>
                <td class="inv">.</td>
            </tr>
            <tr class="setCat">
                <th id="Tab01">{General}</th>
                <th><div></div></th>
            </tr>
            <tr class="setCat">
                <th id="Tab02">{View}</th>
                <th><div></div></th>
            </tr>
            <tr class="setCat">
                <th id="Tab03">{Galaxy}</th>
                <th><div></div></th>
            </tr>
            <tr class="setCat">
                <th id="Tab04">{VacationNDelete}</th>
                <th><div></div></th>
            </tr>
            <tr class="setCat">
                <th id="Tab05">{IgnoreList}</th>
                <th><div></div></th>
            </tr>
            <tr class="setCat">
                <th id="Tab06">{LogonsList}</th>
                <th><div></div></th>
            </tr>
            <tr class="setCat">
                <th id="Tab07">{FleetColors}</th>
                <th><div></div></th>
            </tr>
            <tr>
                <td class="c" style="font-size: 1px;">&nbsp;</td>
            </tr>
            <tr>
                <th class="pad5">
                    <input class="button pad5 w90p" type="submit" value="{SaveAll}"/>
                </th>
            </tr>

            <tr id="fc_pickers" class="hide">
                <th class="pad5 center"></th>
            </tr>
        </table>
    </div>
    <div class="fr tableCont">
        <table class="tableCont visible">
            <tbody style="{DontShowDeleteWarning}">
                <tr>
                    <th class="pad5 lal red" colspan="4">
                        {DeleteMsg}
                    </th>
                </tr>
                <tr class="inv">
                    <td></td>
                </tr>
            </tbody>
            <tbody style="{InfoBoxShow}">
                <tr>
                    <th class="pad5 lal" colspan="4">
                        <ul class="noMarg">{InfoBoxMsgs}</ul>
                    </th>
                </tr>
                <tr class="inv">
                    <td></td>
                </tr>
            </tbody>
        </table>

        <table class="tableCont" id="Cont01">
            <tr>
                <td class="c center" colspan="2">{PasswordChange}</td>
                <td class="c center" colspan="2">{EmailChange}</td>
            </tr>
            <tr>
                <th>{ChangePass}</th>
                <th><input type="checkbox" name="change_pass"/></th>
                <th {EMChange1}>{ChangeEmail}</th>
                <th {EMChange1}><input type="checkbox" name="change_mail"/></th>
                <th colspan="2" {EMChange2}><b class="lime">{EmailPrc_Active_Since}</b></th>
            </tr>
            <tr>
                <th>{OldPassword}</th>
                <th><input tabindex="1" type="password" name="give_oldpass" autocomplete="off" class="pad2 w90p"/></th>
                <th>{OldEmail}</th>
                <th>{ShowOldEmail}</th>
            </tr>
            <tr>
                <th>{NewPassword}</th>
                <th><input tabindex="2" type="password" name="give_newpass" autocomplete="off" class="pad2 w90p"/></th>
                <th>{NewEmail}</th>
                <th {EMChange1}><input tabindex="4" type="text" name="give_newemail" autocomplete="off" class="pad2 w90p"/></th>
                <th {EMChange2}>{EmailPrc_NewEmail}</th>
            </tr>
            <tr>
                <th>{ConfirmPassword}</th>
                <th><input tabindex="3" type="password" name="give_confirmpass" autocomplete="off" class="pad2 w90p"/></th>
                <th {EMChange1}>{ConfirmEmail}</th>
                <th {EMChange1}><input tabindex="5" type="text" name="give_confirmemail" autocomplete="off" class="pad2 w90p"/></th>
                <th {EMChange2}>{StopEmailPrc}</th>
                <th {EMChange2}><input type="checkbox" name="stop_email_change"/></th>
            </tr>
            <tr>
                <td class="c center" colspan="4">{UserInfo}</td>
            </tr>
            <tr>
                <th colspan="4" class="pad5"><a class="help orange" title="{NickChangeInfo}" href="?mode=nickchange">{NickChange}</a></th>
            </tr>
            <tr>
                <th colspan="2"><b class="help dash" title="{AvatarInfo}">{Avatar}</b><br />{AvatarMore}</th>
                <th colspan="2"><input type="text" name="avatar_path" value="{avatar_path}" class="pad2 w90p"/></th>
            </tr>
            <tr>
                <td class="c center" colspan="4">{FleetTransportation}</td>
            </tr>
            <tr>
                <th colspan="2"><b class="help" title="{ResourcesSortingInfo}">{ResourcesSorting}</b></th>
                <th colspan="2">
                    <input type="hidden" name="resSort_changed" value=""/>
                    <input type="hidden" name="resSort_array" value=""/>
                    <ul id="ResSort" class="lal noMarg">{CreateResSortList}</ul>
                </th>
            </tr>
            <tr>
                <th colspan="2"><b class="help dash" title="{QuickResTransInfo}">{QuickResTransport}</b></th>
                <th colspan="2">
                    <select name="quickres_select">{QuickRes_PlanetList}</select>
                </th>
            </tr>
            <tr>
                <td class="c center" colspan="4">{Other}</td>
            </tr>
            <tr>
                <th colspan="2"><b class="dash help" title="{IPCheckDeactiveInfo}">{IPCheckDeactivate}</b></th>
                <th colspan="2"><input type="checkbox" name="ipcheck_deactivate" {ipcheck_deactivate_check}/></th>
            </tr>
        </table>

        <table class="tableCont" id="Cont02">
            <tr>
                <td class="c center" colspan="4">{PlanetSort}</td>
            </tr>
            <tr>
                <th colspan="2">{SortMode}</th>
                <th colspan="2">
                    <select name="planet_sort_mode" class="pad2 w50p">
                        <option value="0" {planet_sort_mode_0}>{PSort_CreationDate}</option>
                        <option value="1" {planet_sort_mode_1}>{PSort_Coordinates}</option>
                        <option value="2" {planet_sort_mode_2}>{PSort_Name}</option>
                    </select>
                </th>
            </tr>
            <tr>
                <th colspan="2">{SortType}</th>
                <th colspan="2">
                    <select name="planet_sort_type" class="pad2 w50p">
                        <option value="0" {planet_sort_type_asc}>{PSort_Asc}</option>
                        <option value="1" {planet_sort_type_desc}>{PSort_Desc}</option>
                    </select>
                </th>
            </tr>
            <tr>
                <th colspan="2"><b class="help dash" title="{SortMoonsInfo}">{SortMoons}</b></th>
                <th colspan="2"><input type="checkbox" name="planet_sort_moons" {planet_sort_moons_check}/></th>
            </tr>
            <tr>
                <td class="c center" colspan="4">{GameView}</td>
            </tr>
            <tr>
                <th colspan="2">{GameLanguage}</th>
                <th colspan="2">
                    <select name="lang" class="center pad2 w90p">{PHP_Insert_LanguageOptions}</select>
                </th>
            </tr>
            <tr>
                <th colspan="2"><b class="help highlight_skin_url highlight_box" title="{SkinPathInfo}"><b class="dash">{SkinPath}</b></b><br />{SkinPathMore}</th>
                <th colspan="2"><input type="text" name="skin_path" value="{skin_path}" class="pad2 w90p"/></th>
            </tr>
            <tr>
                <th colspan="2"><b class="help highlight_skin_select highlight_box" title="{SkinSelectInfo}"><b class="dash">{SkinSelect}</b></b></th>
                <th colspan="2">
                    <select id="skinSelector" class="center pad2 w90p"><option value="">-</option>{ServerSkins}</select>
                </th>
            </tr>
            <tr>
                <th colspan="2"><b class="highlight_skin_use highlight_box">{UseSkin}</b></th>
                <th colspan="2"><input type="checkbox" name="use_skin" {use_skin_check}/></th>
            </tr>
            <tr>
                <th colspan="2"><b class="help dash" title="{PrettyFleetInfo}">{PrettyFleetUse}</b></th>
                <th colspan="2"><input type="checkbox" name="pretty_fleet_use" {pretty_fleet_use_check}/></th>
            </tr>
            <tr>
                <td class="c center" colspan="4">{GameDevelopmentView}</td>
            </tr>
            <tr>
                <th colspan="2"><b class="help" title="{DevelopmentOldInfo}">{DevelopmentOld} (?)</b></th>
                <th colspan="2"><input type="checkbox" name="development_old" {development_old_check}/></th>
            </tr>
            <tr>
                <th colspan="2"><b class="help" title="{BuildExpandedViewInfo}" id="build_expandedviewinfo">{BuildExpandedViewUse} (?)</b></th>
                <th colspan="2"><input type="checkbox" name="build_expandedview_use" {build_expandedview_use_check}/></th>
            </tr>
            <tr>
                <td class="c center" colspan="4">{MessagesView}</td>
            </tr>
            <tr>
                <th colspan="2">{MsgsPerPage}</th>
                <th colspan="2">
                    <select name="msg_perpage" class="pad2">
                        <option class="owht" value="5" {msg_perpage_sel_5}>5</option>
                        <option class="owht" value="10" {msg_perpage_sel_10}>10</option>
                        <option class="owht" value="15" {msg_perpage_sel_15}>15</option>
                        <option class="owht" value="20" {msg_perpage_sel_20}>20</option>
                        <option class="owht" value="25" {msg_perpage_sel_25}>25</option>
                        <option class="owht" value="50" {msg_perpage_sel_50}>50</option>
                        <option class="owht" value="75" {msg_perpage_sel_75}>75</option>
                        <option class="owht" value="100" {msg_perpage_sel_100}>100</option>
                        <option class="owht" value="150" {msg_perpage_sel_150}>150</option>
                        <option class="owht" value="200" {msg_perpage_sel_200}>200</option>
                    </select>
                </th>
            </tr>
            <tr>
                <th colspan="2">{MsgsExpandSpy}</th>
                <th colspan="2"><input type="checkbox" name="msg_spyexpand" {msg_spyexpand_check}/></th>
            </tr>
            <tr>
                <th colspan="2"><b class="help dash" title="{MsgsUseThreadsInfo}">{MsgsUseThreads}</b></th>
                <th colspan="2"><input type="checkbox" name="msg_usethreads" {msg_usethreads_check}/></th>
            </tr>
        </table>

        <table class="tableCont" id="Cont03">
            <tr>
                <td class="c center" colspan="4">{GalaxyFleet}</td>
            </tr>
            <tr>
                <th colspan="2"><b class="help dash" title="{SpyInfo}">{SpyProbes}</b></th>
                <th colspan="2"><input type="text" maxlength="4" name="spy_count" value="{spy_count}" class="w50p"/></th>
            </tr>
            <tr>
                <td class="c center" colspan="4">{GalaxyGenView}</td>
            </tr>
            <tr>
                <th colspan="2">{UseAJAXGalaxy}</th>
                <th colspan="2"><input type="checkbox" name="use_ajaxgalaxy" {use_ajaxgalaxy_check}/></th>
            </tr>
            <tr>
                <th colspan="2">{ShowUserAvatars}</th>
                <th colspan="2"><input type="checkbox" name="show_useravatars" {show_useravatars_check}/></th>
            </tr>
            <tr>
                <td class="c center" colspan="4">{GalaxyShortcuts}</td>
            </tr>
            <tr>
                <th class="vab" colspan="2"><img src="{skinpath}img/e.gif" class="vam"/> <b style="padding-left: 2px;">{SpyAction}</b></th>
                <th colspan="2"><input type="checkbox" name="short_spy" {short_spy_check}/></th>
            </tr>
            <tr>
                <th class="vab" colspan="2"><img src="{skinpath}img/m.gif" class="vam"/> <b style="padding-left: 2px;">{WriteAction}</b></th>
                <th colspan="2"><input type="checkbox" name="short_write" {short_write_check}/></th>
            </tr>
            <tr>
                <th class="vab" colspan="2"><img src="{skinpath}img/b.gif" class="vam"/> <b style="padding-left: 2px;">{BuddyAction}</b></th>
                <th colspan="2"><input type="checkbox" name="short_buddy" {short_buddy_check}/></th>
            </tr>
            <tr>
                <th class="vab" colspan="2"><img src="{skinpath}img/r.gif" class="vam"/> <b style="padding-left: 2px;">{RocketAction}</b></th>
                <th colspan="2"><input type="checkbox" name="short_rocket" {short_rocket_check}/></th>
            </tr>
        </table>

        <table class="tableCont" id="Cont04">
            <tr>
                <td class="c center" colspan="4">{VacationMode}</td>
            </tr>
            <tr>
                <th>{ActivateVacationMode}</th>
                <th><input type="checkbox" name="vacation_activate"/></th>
                <th class="lal" colspan="2" style="width: 60%;">{VacationInfo}<br /><br />{YouWillBeAbleToComeBack}: <span class="skyblue" id="vacationBack">{PHP_Insert_VacationComeback}</span></th>
            </tr>
            <tr>
                <td class="c center" colspan="4">{AccountDelete}</td>
            </tr>
            <tr>
                <th class="padl {delete_active_color}">{DeleteAccount}</th>
                <th><input type="checkbox" name="delete_activate" {delete_activate_check}/></th>
                <th class="pad lal" colspan="2" rowspan="2">{DeleteInfo}</th>
            </tr>
            <tr {DeleteConfirmShow}>
                <th>{PassConfirm}</th>
                <th><input type="password" name="delete_confirm" autocomplete="off" class="w90p"/></th>
            </tr>
            <tr {DeleteClickToRemoveShow}>
                <th class="orange" colspan="2">{ClickToRemoveDeletion}</th>
            </tr>
        </table>

        <table class="tableCont" id="Cont05">
            <tr>
                <td class="c center" colspan="4">{WhatIsIgnoreList}</td>
            </tr>
            <tr>
                <th class="pad2" colspan="4" style="padding-left: 20px; text-align: left;">{IgnoreList_FullInfo}</th>
            </tr>
            <tr>
                <td class="c center" colspan="4">{AddUserToIgnoreList}</td>
            </tr>
            <tr>
                <th class="c center">{Ignore_Username}</th>
                <th class="c center" colspan="2"><input type="text" name="ignore_username" class="w90p pad2"/></th>
                <th class="c center"><input type="submit" class="w90p pad2 button saveType_Ignore" value="{IgnoreUserNow}"/></th>
            </tr>
            <tr>
                <td class="c center" colspan="4">{ManageIgnoreList}</td>
            </tr>
            <tr {IgnoreList_Hide1Del}>
                <th class="c center" colspan="4"><input type="submit" class="pad2 button saveType_DelIgnore" value="{DeleteFromIgnoreList}" /></th>
            </tr>
            <tr>
                <th class="c center" colspan="4">
                    <div style="width: 55%; margin: 0% 20% 0% 25%; text-align: left;">{ParseIgnoreList}</div>
                </th>
            </tr>
            <tr {IgnoreList_Hide2Del}>
                <th class="c center" colspan="4"><input type="submit" class="pad2 button saveType_DelIgnore" value="{DeleteFromIgnoreList}" /></th>
            </tr>
        </table>

        <table class="tableCont" id="Cont06">
            <tr>
                <td class="c center" colspan="4">{WhatIsLogonsList}</td>
            </tr>
            <tr>
                <th class="pad2" colspan="4">{LogonsList_FullInfo}</th>
            </tr>
            <tr>
                <td class="c center" colspan="2">{Logons_Date}</td>
                <td class="c center">{Logons_IP}</td>
                <td class="c center">{Logons_Status}</td>
            </tr>
            {ParseLogonsList}
        </table>

        <table class="tableCont" id="Cont07">
            <tr>
                <td class="c center" colspan="6">{WhatIsFleetColors}</td>
            </tr>
            <tr>
                <th class="pad5" colspan="6">{FleetColors_Info}</th>
            </tr>
            <tr>
                <td class="c center" colspan="2" style="width: 30%;">{FleetColors_Pickers_OwnFlying}</td>
                <td class="c center" colspan="2" style="width: 30%;">{FleetColors_Pickers_OwnComingBack}</td>
                <td class="c center" colspan="2" style="width: 30%;">{FleetColors_Pickers_NotOwn}</td>
            </tr>
            {Insert_FleetColors_Pickers}
        </table>
    </div>
</div>
</form>
