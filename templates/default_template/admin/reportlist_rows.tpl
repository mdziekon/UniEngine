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
        <a href="../messages.php?mode=write&uid={sender_id}&amp;subject={msg_answer_subject}&amp;insert={msg_answer_input}"><img border="0" title="Wyślij wiadomość" alt="[PW]" src="../skins/epicblue/img/m.gif"/></a>
        <a href="?showall={showall}&amp;action=delete&amp;id={data_id}"><img src="../images/delete.png" title="Usuń raport" alt="Usuń raport"/></a>
		<br/>
        <a href="?showall={showall}&amp;action=change_status&amp;id={data_id}&amp;set_status=9"><img src="../images/false.png" title="Ustaw jako sprawdzone - odrzucone" alt="Ustaw jako sprawdzone - odrzucone"/></a> 
        <a href="?showall={showall}&amp;action=change_status&amp;id={data_id}&amp;set_status=10"><img src="../images/true.png" title="Ustaw jako wykonane" alt="Ustaw jako wykonane"/></a>
		<br/>
		<a class="{Hide_NoBash}" href="bashDetector.php?sender=[{report_user}]&amp;owner=[{sender_id}]&amp;date={data_datebash}"><img src="../images/eye.png" title="Analiza Ataków" alt="Analiza Ataków"/></a>
		<a href="banuser.php?ids={report_user}"><img src="../images/lock.png" title="Zbanuj Gracza" alt="Zbanuj Gracza"/></a>
    </th>
</tr>