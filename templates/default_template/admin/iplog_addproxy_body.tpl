<br/>
<form action="" method="post">
    <input type="hidden" name="sent" value="1"/>
    <table style="width: 800px;">
        <tr>
            <td class="c" colspan="2">{PageTitle}</td>
        </tr>
        {Insert_MsgBox}
        <tr>
            <th class="pad5" style="width: 150px;">{Table_List}</th>
            <th class="pad5"><textarea class="pad5" name="list" style="height: 100px;"></textarea></th>
        </tr>
        <tr>
            <th style="padding: 3px;" colspan="2">
                <input class="lime" style="width: 95%; font-weight: 700; padding: 3px;" type="submit" value="{Table_Submit}"/>
            </th>
        </tr>
    </table>
</form>
