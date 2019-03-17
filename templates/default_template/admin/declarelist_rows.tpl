<tr>
    <th><a href="userlist.php?uid={UserID}">{Username} (#{UserID})<a></th>
    <th>{CreateOtherPlayers}</th>
    <th>{DeclarationDate}</th>
    <th>{DeclarationReason}</th>
    <th>{DeclarationStatus}</th>
    <th>
        <a href="userlist.php?search_user={UsersIDList}&search_by=uid"><img src="../images/search.png" title="{btn_search__tooltip}" alt="{btn_search__alt}"/></a>
        <a href="?action=delete&id={declaration_id}"><img src="../images/r1.png" title="{btn_soft_delete_declaration__tooltip}" alt="{btn_soft_delete_declaration__alt}"/></a>
        <a href="?action=accept&id={declaration_id}"><img src="../images/true.png" title="{btn_reject_declaration__tooltip}" alt="{btn_reject_declaration__alt}"/></a>
        <a href="?action=refuse&id={declaration_id}"><img src="../images/false.png" title="{btn_accept_declaration__tooltip}" alt="{btn_accept_declaration__alt}"/></a>
    </th>
</tr>
