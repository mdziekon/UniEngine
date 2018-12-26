<br />
<form action="" method="post">
    <input type="hidden" name="action" value="do"/>
    <table style="width: 600px">
        <tr>
            <td class="c" colspan="2">{Rename_Title}<span style="float: right;">(<a class="orange" href="overview.php">{Rename_GoBack}</a>)</span></td>
        </tr>
        <tr {Rename_Ins_MsgHide}>
            <th colspan="2" class="pad5 {Rename_Ins_MsgColor}">{Rename_Ins_MsgTxt}</th>
        </tr>
        <tr>
            <th class="pad5" style="width: 200px;">
                {Rename_CurrentName}
            </th>
            <th class="pad5" style="width: 400px;">
                {Rename_Ins_CurrentName}
            </th>
        </tr>
        <tr>
            <th class="pad5">
                {Rename_NewName}
            </th>
            <th class="pad5">
                <input type="text" name="set_newname" maxlength="20" style="width: 200px; padding: 3px;"/>
            </th>
        </tr>
        <tr>
            <th colspan="2" class="pad5">
                <input type="submit" value="{Rename_Submit}" class="pad5" style="font-weight: bold;"/>
            </th>
        </tr>
    </table>
</form>
