<script src="../libs/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="../libs/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js" type="text/javascript"></script>
<script src="../libs/jquery-ui-datepicker-{JS_DatePicker_TranslationLang}/jquery-ui-datepicker-{JS_DatePicker_TranslationLang}.min.js" type="text/javascript"></script>
<script src="../dist/js/admin/SFBSettings_body_manage.cachebuster-1561455380555.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="../libs/jquery-ui/jquery-ui.min.css" />
<link rel="stylesheet" type="text/css" href="../dist/css/admin/SFBSettings_body_manage.cachebuster-1546567692327.min.css" />
<br/>
<form action="" method="post">
<input type="hidden" name="action" value="save"/>
<table style="width: 1050px;">
    <tbody class="{Insert_HideMsgBox}">
        <tr>
            <th class="pad5 {Insert_MsgBoxColor}" colspan="2">{Insert_MsgBoxText}</th>
        </tr>
        <tr>
            <th class="inv" style="font-size: 2px;">&nbsp;</th>
        </tr>
    </tbody>
    <tr>
        <td class="c pad5" colspan="2">
            {SFB_Header_Adding}
        </td>
    </tr>
    <tr>
        <td class="c pad5 tRight" style="width: 200px;">{SFB_Labels_Type}</td>
        <th class="pad5 tLeft">
            <select name="type" class="pad5" {Insert_Disable_TypeSelect}>
                <option value="1" {Insert_Form_Select_Type_1}>{SFB_LabelsData_Type_1}</option>
                <option value="2" {Insert_Form_Select_Type_2}>{SFB_LabelsData_Type_2}</option>
                <option value="3" {Insert_Form_Select_Type_3}>{SFB_LabelsData_Type_3}</option>
            </select>
        </th>
    </tr>
    <tr>
        <td class="c pad5 tRight">{SFB_Labels_Missions}</td>
        <th class="pad5 tLeft">
            <div style="border-bottom: dashed white 1px; padding-bottom: 2px; margin-bottom: 4px;">
                <div class="missionCheck"><input type="checkbox" id="missionAll"/><label for="missionAll">{SFB_LabelsData_Mission_All}</label></div>
                <div class="missionCheck"><input type="checkbox" id="missionMilitary"/><label for="missionMilitary">{SFB_LabelsData_Mission_Military}</label></div>
                <div class="missionCheck"><input type="checkbox" id="missionCivil"/><label for="missionCivil">{SFB_LabelsData_Mission_Civil}</label></div>
            </div>
            {Insert_MissionSelectors}
        </th>
    </tr>
    <tr>
        <td class="c pad5 tRight">{SFB_Labels_StartTime}</td>
        <th class="pad5 tLeft">
            <input type="text" class="w150px pad2 margR5px" name="startTime_date" value="{Insert_startTime_date}" {Insert_Disable_StartTime}/> {Insert_Current_StartTime}
        </th>
    </tr>
    <tr>
        <td class="c pad5 tRight">{SFB_Labels_EndTime}</td>
        <th class="pad5 tLeft">
            <input type="text" class="w150px pad2 margR5px" name="endTime_date" value="{Insert_endTime_date}" {Insert_Disable_EndTime}/> {Insert_Current_EndTime}
        </th>
    </tr>
    <tr id="PostEndTimePicker">
        <td class="c pad5 tRight">{SFB_Labels_PostEndTime}</td>
        <th class="pad5 tLeft">
            <span class="fl"><input type="text" class="w150px pad2 margR5px" name="postEndTime_date" value="{Insert_postEndTime_date}" {Insert_Disable_PostEndTime}/> {Insert_Current_PostEndTime}</span>
            <span class="fr"><input type="button" class="pad2 button" id="postEndTime_Zero" value="{SFB_LabelsData_PostEndTime_Zero}" {Insert_Disable_PostEndTime}/> <input type="button" class="pad2 button" id="postEndTime_1Day" value="{SFB_LabelsData_PostEndTime_1Day}" {Insert_Disable_PostEndTime}/></span>
        </th>
    </tr>
    <tr id="ElementIDPicker">
        <td class="c pad5 tRight">{SFB_Labels_ElementID}</td>
        <th class="pad5 tLeft">
            <input type="text" class="pad2" name="elementID" value="{Insert_Form_ElementID}" {Insert_Disable_ElementID}/>
        </th>
    </tr>
    <tr id="DontBlockIfIdlePicker">
        <td class="c pad5 tRight">{SFB_Labels_DontBlockIfIdle}</td>
        <th class="pad5 tLeft">
            <input type="checkbox" name="dontBlockIfIdle" {Insert_Form_Select_DontBlockIfIdle}/>
        </th>
    </tr>
    <tr>
    <td class="c pad5 tRight">{SFB_Labels_Reason}</td>
    <th class="pad5 tLeft">
        <textarea class="pad2" name="reason" style="width: 50%; height: 120px;">{Insert_Form_Reason}</textarea>
    </th>
    </tr>
    <tr>
    <td class="c pad5 center" colspan="2">
        <input type="submit" class="pad5 lime" style="font-weight: bold; width: 120px;" value="{Insert_SubmitButton}"/>
        <input type="button" class="pad5 orange" id="goBack" style="font-weight: bold; width: 120px;" value="{SFB_Labels_CancelChanges}"/>
    </td>
    </tr>
</table>
</form>
