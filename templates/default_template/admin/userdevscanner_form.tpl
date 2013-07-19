<style>
.hide {
    display: none;
}
.inv {
    visibility: hidden;
}
.pad {
    padding: 5px;
}
.pad2 {
    padding: 2px;
}
.w200 {
    width: 200px;
}
.w400 {
    width: 400px;
}
</style>
<br />
<form action="" method="post">
    <table width="600">
        <tbody{PHP_ShowError}>
            <tr>
                <th colspan="2" class="pad red">{Error_Found}</th>
            </tr>
            <tr><th colspan="2" class="inv">&nbsp;</th></tr>
        </tbody>
        <tr>
            <td colspan="2" class="c">{Table_Header}</td>
        </tr>
        <tr>
            <th class="w200 pad2">
                {Table_Form_UID}
            </th>
            <th class="w400 pad2">
                <input type="text" name="uid" class="w200 pad2"/>
            </th>
        </tr>
        <tr>
            <th class="pad2">
                {Table_Form_Username}
            </th>
            <th class="pad2">
                <input type="text" name="username" class="w200 pad2"/>
            </th>
        </tr>
        <tr>
            <th colspan="2"  class="pad2">
                <input type="submit" value="{Table_Form_Submit}"/>
            </th>
        </tr>
    </table>
</form>