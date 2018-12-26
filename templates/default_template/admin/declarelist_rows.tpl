<tr>
    <th><a href="userlist.php?uid={UserID}">{Username} (#{UserID})<a></th>
    <th>{CreateOtherPlayers}</th>
    <th>{DeclarationDate}</th>
    <th>{DeclarationReason}</th>
    <th>{DeclarationStatus}</th>
    <th>
        <a href="userlist.php?search_user={UsersIDList}&search_by=uid"><img src="../images/search.png"/></a>
        <a href="?action=delete&id={declaration_id}"><img src="../images/r1.png" title="Usuń zgłoszenie Gracza #{UserID}" alt="Usuń zgłoszenie Gracza #{UserID}"/></a>
        <a href="?action=accept&id={declaration_id}"><img src="../images/true.png" title="Akceptuj zgłoszenie Gracza #{UserID}" alt="Akceptuj zgłoszenie Gracza #{UserID}"/></a>
        <a href="?action=refuse&id={declaration_id}"><img src="../images/false.png" title="Odrzuć zgłoszenie Gracza #{UserID}" alt="Odrzuć zgłoszenie Gracza #{UserID}"/></a>
    </th>
</tr>
