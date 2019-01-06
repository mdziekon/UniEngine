<style>
.hide {
    display: none;
}
</style>
<br />
<table width="1000" style="color:#FFFFFF">
        {system_msg}
    <tr>
        <td class="c" colspan="9"><span style="float: left;">{Report_list_list} [<a href="?showall=1">{Report_show_all}</a>/<a href="?showall=0">{Report_show_no_made}</a>]</span><span style="float: right">[<a href="?deleteall=yes" style="color: red;">{Report_list_clear}</a>]</span></td>
    </tr>
    <tr>
        <th>{Report_ID}</th>
        <th>{Report_Date}</th>
        <th>{Report_Sender}</th>
        <th>{Report_Type}</th>
        <th>{Report_Element}</th>
        <th>{Report_User}</th>
        <th>{Report_Info}</th>
        <th>{Report_Status}</th>
        <th width="80px">{Report_Actions}</th>
    </tr>
        {adm_ul_table}
</table>
