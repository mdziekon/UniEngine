<script>
var $MaxLength_Text = {FormInsert_MaxSigns};
</script>
<script src="../dist/js/admin/massmessage.cachebuster-1561475785611.min.js" type="text/javascript"></script>
<link href="../dist/css/admin/massmessage.cachebuster-1561475785613.min.css" rel="stylesheet" type="text/css" />
<br><br>
<form
    id="thisForm"
    action="?mode=change"
    method="post"
>
    <table width="519">
        <tr>
            <td class="c" colspan="2">{MassMessage_Form_Title}</td>
        </tr>
        <tr>
            <th>{MassMessage_Form_Subject}</th>
            <th class="pad2">
                <input
                    type="text"
                    name="subject"
                    class="pad3 w90p"
                    maxlength="100"
                    value=""
                />
            </th>
        </tr>
        <tr>
            <th class="pad2">
                {MassMessage_Form_Content}<br />
                ({MassMessage_Form_ContentLimit}: <span id="charCounter">0</span> / {FormInsert_MaxSigns})<br/>
                (<a id="thisReset">{MassMessage_Form_Reset}</a>)
            </th>
            <th class="pad2">
                <textarea name="text" id="textBox"></textarea>
            </th>
        </tr>
        <tr>
            <th colspan="2" class="pad2">
                <input type="submit" value="{MassMessage_Form_Submit}" class="pad3" style="font-weight: 700; width: 150px;"/>
            </th>
        </tr>
    </table>
</form>
