<tr>
    <th>{data_id}</th>
    <th>{data_date}</th>
    <th>{data_sender}</th>
    <th>{data_type}</th>
    <th>{data_element}</th>
    <th>{data_user}</th>
    <th style="text-align: left;">{data_info}</th>
    <th>{data_status}</th>
    <th>
        <a href="../messages.php?mode=write&uid={sender_id}&amp;subject={msg_answer_subject}&amp;insert={msg_answer_input}"><img border="0" title="{btn_send_message__tooltip}" alt="{btn_send_message__alt}" src="../skins/epicblue/img/m.gif"/></a>
        <a href="?showall={showall}&amp;action=delete&amp;id={data_id}"><img src="../images/delete.png" title="{btn_delete_report__alt}" alt="{btn_delete_report__alt}"/></a>
        <br/>
        <a href="?showall={showall}&amp;action=change_status&amp;id={data_id}&amp;set_status=9"><img src="../images/false.png" title="{btn_review_and_reject__tooltip}" alt="{btn_review_and_reject__alt}"/></a>
        <a href="?showall={showall}&amp;action=change_status&amp;id={data_id}&amp;set_status=10"><img src="../images/true.png" title="{btn_review_and_accept__tooltip}" alt="{btn_review_and_accept__alt}"/></a>
        <br/>
        <a class="{Hide_NoBash}" href="bashDetector.php?sender=[{report_user}]&amp;owner=[{sender_id}]&amp;date={data_datebash}"><img src="../images/eye.png" title="{btn_check_attacks__tooltip}" alt="{btn_check_attacks__alt}"/></a>
        <a href="banuser.php?ids={report_user}"><img src="../images/lock.png" title="{btn_ban_reported_player__tooltip}" alt="{btn_ban_reported_player__alt}"/></a>
    </th>
</tr>
