<h2>{Title}</h2>
<form action="declaration.php" method="post">
    <input type="hidden" name="mode" value="addit"/>
    <table width="500">
        <tr style="{ShowError}">
            <th class="pad5 {ErrorColor}" colspan="2">
                {ErrorText}
            </th>
        </tr>
        <tr style="visibility: hidden; {ShowError}">
            <td></td>
        </tr>
        <tr>
            <th class="pad5" colspan="2">
                {DeclarationInfo}
            </th>
        </tr>
        <tr>
            <th class="pad5" colspan="2">
                <textarea name="userslist">{InsertUserslist}</textarea>
            </th>
        </tr>
        <tr>
            <th class="pad5">{DeclarationType}</th>
            <th class="pad5" style="text-align: left">
                <input type="radio" name="declaration_type" value="1" {InsertType_1}/> {OnePC}<br />
                <input type="radio" name="declaration_type" value="2" {InsertType_2}/> {OneIP}<br />
                <input type="radio" name="declaration_type" value="3" {InsertType_3}/> {SamePlaces}<br />
                <input type="radio" name="declaration_type" value="4" {InsertType_4}/> {Others}<br />
                <input type="radio" name="declaration_type" value="5" {InsertType_5}/> {OccasionalLogons}<br />
            </th>
        </tr>
        <tr>
            <th colspan="2" class="pad5"><input type="submit" value="{SubmitDeclaration}" /></th>
        </tr>
    </table>
</form>
