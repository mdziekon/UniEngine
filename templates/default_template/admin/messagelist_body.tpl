{mlst_scpt}
<script>
var SpyExpanded = false;
var JS_Lang = {'mess_convert_title': '{mess_convert_title}', 'mess_report_e_title': '{mess_report_e_title}', 'mess_delete_single_title': '{mess_delete_single_title}',
    'mess_reply_title': '{mess_reply_title}', 'mess_ignore_title': '{mess_ignore_title}', 'mess_report_title': '{mess_report_title}', 'mess_selectall_title': '{mess_selectall_title}',
    'Sure_WantDeleteAll': '{Sure_WantDeleteAll}', 'Sure_WantDeleteCat': '{Sure_WantDeleteCat}', 'tip_Deleted': '{tip_Deleted}', 'tip_Read': '{tip_Read}', 'tip_Copy': '{tip_Copy}'};
</script>
<script src="../dist/js/messages.cachebuster-1545956361123.min.js"></script>
<script src="../dist/js/admin/messagelist.cachebuster-1545956361123.min.js"></script>
<link rel="stylesheet" type="text/css" href="../dist/css/messages.cachebuster-1546565145290.min.css" />
<link rel="stylesheet" type="text/css" href="../dist/css/admin/messagelist.cachebuster-1546564327123.min.css" />
<br />
<form action="" method="post" id="formID">
    <input type="hidden" name="curr" value="{mlst_data_page}"/>
    <input type="hidden" name="pmax" value="{mlst_data_pagemax}"/>
    <input type="hidden" name="sele" value="{mlst_data_sele}"/>
    <input type="hidden" name="stay" value=""/>
    <table width="1000">
        <tr>
            <td class="c" colspan="4">{mlst_title}</td>
        </tr>
        <tr>
            <th style="width: 245px;">&nbsp;</th>
            <th style="width: 245px;">{mlst_hdr_type}</th>
            <th style="width: 245px;"><select name="type" onchange="submit();">{mlst_data_types}</select></th>
            <th style="width: 245px;">&nbsp;</th>
        </tr>
        <tr>
            <th>{mlst_hdr_userID}</th>
            <th><input type="text" name="user_id" value="{selected_user_id}"/></th>
            <th>{mlst_hdr_msgID}</th>
            <th><input type="text" name="msg_id"/></th>
        </tr>
        <tr>
            <th colspan="4"><input type="submit" name="filter" value="{mlst_hdr_filter}" style="font-weight: 700; width: 100px;"/></th>
        </tr>
        <tr>
            <th colspan="4" style="font-size: 0px;">&nbsp;</th>
        </tr>
        <tr>
            <th><input type="submit" name="prev" value="&#171;&#171;" style="font-weight: bolder; width: 100px;" /></th>
            <th>{mlst_hdr_page}</th>
               <th><input type="text" name="page_input" autocomplete="off" style="width: 50px; margin-right: 15px;"/> <span class="lime">{_PagesCurrent_Pretty}</span> {Pages_of} {_PagesTotalCount_Pretty}</th>
            <th><input type="submit" name="next" value="&#187;&#187;" style="font-weight: bolder; width: 100px;" /></th>
        </tr>
    </table>
    <table width="1000" style="margin-top: 10px;">
        <tr align="center" valign="middle">
            <td class="c" style="width: 50px;">{mlst_hdr_id}</td>
            <td class="c" style="width: 80px;">{mlst_hdr_time}</td>
            <td class="c" style="width: 100px;">{mlst_hdr_from}</td>
            <td class="c" style="width: 100px;">{mlst_hdr_to}</td>
            <td class="c" style="width: 20px;">{mlst_hdr_status}</td>
            <td class="c" style="width: 645px;">{mlst_hdr_text}</td>
        </tr>
        <tr{HideSelectedActionRow}>
            <th class="pad5"><input type="checkbox" class="selAll"/></th>
            <th colspan="5" class="pad5">
                <input type="submit" name="setsel_read" value="{mlst_bt_setread}" style="font-weight: bold; color: lime;" />&nbsp;
                <input type="submit" name="setsel_notread" value="{mlst_bt_setnotread}" style="font-weight: bold; color: orange;" />&nbsp;
                <input type="submit" name="delsel_soft" value="{mlst_bt_delselsoft}" style="font-weight: bold; color: orange;" />&nbsp;
                <input type="submit" name="delsel_hard" value="{mlst_bt_delsel}" style="font-weight: bold; color: red;" />
            </th>
        </tr>
        <tbody id="msgRows">{mlst_data_rows}</tbody>
        <tr{HideSelectedActionRow}>
            <th class="pad5"><input type="checkbox" class="selAll"/></th>
            <th colspan="5" class="pad5">
                <input type="submit" name="setsel_read" value="{mlst_bt_setread}" style="font-weight: bold; color: lime;" />&nbsp;
                <input type="submit" name="setsel_notread" value="{mlst_bt_setnotread}" style="font-weight: bold; color: orange;" />&nbsp;
                <input type="submit" name="delsel_soft" value="{mlst_bt_delselsoft}" style="font-weight: bold; color: orange;" />&nbsp;
                <input type="submit" name="delsel_hard" value="{mlst_bt_delsel}" style="font-weight: bold; color: red;" />
            </th>
        </tr>
    </table>
    <table width="1000" style="margin-top: 10px;">
        <tr>
            <th style="width: 245px;"><input type="submit" name="prev" value="&#171;&#171;" style="font-weight: bolder; width: 100px;" /></th>
            <th style="width: 245px;">{mlst_hdr_page}</th>
            <th style="width: 245px;"><input type="text" name="page_input" autocomplete="off" style="width: 50px; margin-right: 15px;"/> <span class="lime">{_PagesCurrent_Pretty}</span> {Pages_of} {_PagesTotalCount_Pretty}</th>
            <th style="width: 245px;"><input type="submit" name="next" value="&#187;&#187;" style="font-weight: bolder; width: 100px;" /></th>
        </tr>
    </table>
</form>
