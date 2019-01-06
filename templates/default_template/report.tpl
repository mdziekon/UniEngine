<script>
var addInfo = new Array();

addInfo[0] = '{add_info_user_badmsg}';
addInfo[1] = '{add_info_bash}';
addInfo[2] = '{add_info_push}';
addInfo[3] = '{add_info_user_other}';
addInfo[4] = '{add_info_sys_badmsg}';
addInfo[5] = '{add_info_sys_error}';

function handleSelect(index){
    if(index == 4 || index == 5 || index == 6){
        toggleUsernameInput(false);
    } else {
        toggleUsernameInput(true);
    }
    setAddInfo = document.getElementById('add_info');
    if(addInfo[index] !== undefined){
        setAddInfo.style.textAlign = 'left';
        setAddInfo.style.paddingLeft = '20px';
        setAddInfo.innerHTML = addInfo[index];
    } else {
        setAddInfo.style.textAlign = 'center';
        setAddInfo.style.paddingLeft = '5px';
        setAddInfo.innerHTML = '{add_info_none}';
    }
}

function toggleUsernameInput(switchon){
    var element = document.getElementById('reported_username');
    if(switchon === true){
        element.disabled = false;
    } else {
        element.disabled = true;
    }
}
</script>
<br/>
<form action="" method="post">
    <input type="hidden" name="mode" value="send_report"/>
    <input type="hidden" name="eid" value="{get_eid}{post_eid}"/>
    <table width="600">
        <tbody>
            <tr>
                <td class="c" colspan="2"><span style="background: url('images/warning.png') no-repeat 0 0pt; padding-left: 20px;">{Table_title}</span></td>
            </tr>
            <tr>
                <th colspan="2">&nbsp;{Report_send_result}</th>
            </tr>
            <tr>
                <th style="width: 40%;" class="pad5">{Report_type}</th>
                <th style="width: 60%;">
                    <select id="typeSelector" name="type" onchange="handleSelect(this.selectedIndex);" onkeyup="handleSelect(this.selectedIndex);">
                        <option value="user_badmsg" {Input_HideType1} {select_type_user_badmsg}>{Type_user_badmsg}</option>
                        <option value="user_bash" {select_type_user_bash}>{Type_user_bash}</option>
                        <option value="user_push" {select_type_user_push}>{Type_user_push}</option>
                        <option value="user_other" {select_type_user_other}>{Type_user_other}</option>
                        <option value="sys_badmsg" {select_type_sys_badmsg}>{Type_sys_badmsg}</option>
                        <option value="sys_error" {select_type_sys_error}>{Type_sys_error}</option>
                        <option value="mail_smtp" {select_type_mail_smtp}>{Type_mail_smtp}</option>
                        <option value="other" {select_type_other}>{Type_other}</option>
                        <option value="user_badmsg_chat" {Input_HideType1} {select_type_user_badmsg_chat}>{Type_user_badmsg_chat}</option>
                    </select>
                </th>
            </tr>
            <tr>
                <th>{Additional_info}</th>
                <th id="add_info" style="text-align: center; padding: 5px; font-size: 10px; height: 150px;"></th>
            </tr>
            <tr>
                <th>{Report_Username}</th>
                <th class="pad5">
                    <input id="reported_username" type="text" name="reported_username" disabled="" value="{get_uid}{post_reported_username}" style="width: 100%;"/>
                </th>
            </tr>
            <tr>
                <th>{User_info}</th>
                <th class="pad5">
                    <textarea name="user_info" style="width: 100%; height: 75px;">{post_user_info}</textarea>
                </th>
            </tr>
            <tr>
                <th colspan="2">&nbsp;</th>
            </tr>
            <tr>
                <th colspan="2" class="pad5"><input type="submit" class="pad5 red" style="font-weight: 700;" value="{Send_report}"/></th>
            </tr>
        </tbody>
    </table>
</form>
<script>
handleSelect(document.getElementById('typeSelector').selectedIndex);
</script>
