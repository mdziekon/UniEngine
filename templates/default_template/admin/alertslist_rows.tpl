<tr>
    <th>{ID}</th>
    <th>{ThisDate}<br/>{ThisTime}<br/><br/>{Sender}</th>
    <th>{Type}</th>
    <th>{Importance}</th>
    <th class="tleft rowData">{Data}</th>
    <th>{Status}</th>
    <th>
        <a class="delete tipTitle" href="?action=delete&amp;id={ID}"></a><br/>
        <a class="users tipTitle" href="userlist.php?search_user={MainUsers}&search_by=uid"></a><br/>
        <a class="search tipTitle" href="userlist.php?search_user={AllUsers}&search_by=uid"></a>
    </th>
</tr>
