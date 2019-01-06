<style>.hide{visibility: hidden;}</style>
<br />
<form action="unbanuser.php" method="post">
    <input type="hidden" name="send" value="yes"/>
    <table style="width: 600px;">
        <tbody{HideInfoBox}>
            <tr>
                <th class="pad5 {InsertInfoBoxColor}" colspan="2">{InsertInfoBoxText}</th>
            </tr>
            <tr class="hide"><td style="min-height: 5px;"></td></tr>
        </tbody>
        <tr>
            <td class="c" colspan="2">{Page_Title}</td>
        </tr>
        <tr>
            <th style="width: 200px;" class="pad5">{Form_UserInput}</th>
            <th style="width: 400px;" class="pad5">
                <textarea name="users" style="width: 375px; padding: 3px;">{Insert_SearchBox}{InsertUsernames}</textarea>
            </th>
        </tr>
        <tr>
            <td class="c" colspan="2">{Form_Other}</td>
        </tr>
        <tr>
            <th class="pad2">{Form_RemoveVacation}</th>
            <th class="pad2">
                <input type="checkbox" name="vacoff"/>
            </th>
        </tr>
        <tr class="hide"><td style="min-height: 5px;"></td></tr>
        <tr>
            <th class="pad5" colspan="2">
                <input type="submit" value="{Form_Unban}" class="pad5" style="font-weight: 700;"/>
            </th>
        </tr>
    </table>
</form>
