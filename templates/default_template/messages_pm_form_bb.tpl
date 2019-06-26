<script>
var $MaxLength_Text = {FormInsert_MaxSigns};
</script>
<script src="dist/js/messages_form.cachebuster-1545956361123.min.js" type="text/javascript"></script>
<link href="dist/css/messages_form.cachebuster-1546564327123.min.css" rel="stylesheet" type="text/css" />
<br />
<form action="messages.php?mode=write" method="post" id="thisForm">
    <input type="hidden" name="send_msg" value="1"/>
    <input type="hidden" name="uid" value="{FormInsert_uid}"/>
    <input type="hidden" name="replyto" value="{FormInsert_replyto}"/>
    <table width="519">
        <tr class="{Insert_HideMsgBox}">
            <th colspan="2" class="pad5">{Insert_MsgBoxText}</th>
        </tr>
        <tr><td class="inv invBr"></td></tr>
        <tr>
            <td class="c" colspan="2">{Form_SendMsg}</td>
        </tr>
        <tr>
            <th style="width: 160px;">{Form_User}</th>
            <th class="pad2">
                <input type="text" name="uname" class="pad3 w90p" value="{FormInsert_username}" {FormInsert_LockUsername}/>
            </th>
        </tr>
        <tr>
            <th>{Form_Subject}</th>
            <th class="pad2">
                <input type="text" name="subject" class="pad3 w90p" maxlength="100" value="{FormInsert_subject}" {FormInsert_LockSubject}/>
            </th>
        </tr>
        <tr style="{FormInsert_displaySendAsAdmin}">
            <th colspan="2" class="pad5 t_l" style="padding-left: 10px !important;">
                <input type="checkbox" name="send_as_admin_msg" style="vertical-align: text-bottom; margin: 0px;" {FormInsert_checkSendAsAdmin}/> {Form_SendAsAdmin}
            </th>
        </tr>
        <tr>
            <th class="pad2">
                {Form_Text}<br />
                ({Form_MaxSigns}: <span id="charCounter">0</span> / {FormInsert_MaxSigns})<br/>
                (<a id="thisReset">{Form_Reset}</a>)
            </th>
            <th class="pad2">
                <textarea name="text" id="textBox">{FormInsert_text}</textarea>
            </th>
        </tr>
        <tr>
            <th colspan="2" class="pad2">
                <input type="submit" value="{Form_Send}" class="pad3" style="font-weight: 700; width: 150px;"/>
            </th>
        </tr>
        <tr><td class="inv invBr"></td></tr>
        <tr>
            <td class="c" colspan="2">{Form_BBCode}</td>
        </tr>
        <tr>
            <th class="t_r p_r">{bbcode_b}</th>
            <th class="t_l p_l">[b]Text[/b]</th>
        </tr>
        <tr>
            <th class="t_r p_r">{bbcode_u}</th>
            <th class="t_l p_l">[u]Text[/u]</th>
        </tr>
        <tr>
            <th class="t_r p_r">{bbcode_i}</th>
            <th class="t_l p_l">[i]Text[/i]</th>
        </tr>
        <tr>
            <th class="t_r p_r">{bbcode_img}</th>
            <th class="t_l p_l">[img]http://example.com/image.png[/img]</th>
        </tr>
        <tr>
            <th class="t_r p_r">{bbcode_url}</th>
            <th class="t_l p_l">[url=http://example.com]Example page[/url]</th>
        </tr>
        <tr>
            <th class="t_r p_r">{bbcode_url}</th>
            <th class="t_l p_l">[url]http://example.com[/url]</th>
        </tr>
        <tr>
            <th class="t_r p_r">{bbcode_coords}</th>
            <th class="t_l p_l">[coord=2:35:7]</th>
        </tr>
    </table>
</form>
