<style>
.hide {
    display: none;
}
.rowLeft {
    text-align: left;
    padding-left: 15px;
}
</style>
<br /><table width="900">
    <tr>
        <td class="c" colspan="4"><a href="Telemetry.php">{Page_Title}</a> &#187; <a href="Telemetry.php">{Tele_Header_Overview}</a></td>
    </tr>
    <tr class="{Hide_Headers}">
        <td class="c center" style="width: 50px;">{Tele_Headers_ID}</td>
        <td class="c center" style="width: 500px;">{Tele_Headers_Place}</td>
        <td class="c center" style="width: 100px;">{Tele_Headers_HasPost}</td>
        <td class="c center" style="width: 200px;">{Tele_Headers_DataPoints}</td>
    </tr>
    <tr class="{Hide_MessageBox}">
        <th class="pad2 {MessageBox_Color}" colspan="4">{MessageBox_Text}</th>
    </tr>
    {Places}
</table>
