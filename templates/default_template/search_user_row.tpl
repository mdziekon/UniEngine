<tr>
    <th><a href="profile.php?uid={id}">{username}</a></th>
    <th>
        <a href="messages.php?mode=write&amp;uid={id}" title="{write_a_messege}"><img src="{skinpath}img/m.gif" alt="{write_a_messege}"/></a>
        <a href="buddy.php?cmd=add&amp;uid={id}"><img src="{skinpath}img/b.gif" alt="{buddy_request}" title="{buddy_request}" border="0"/></a>
        <a style="{Insert_HideAllyInvite}" href="alliance.php?mode=invite&amp;uid={id}" title="{Ally_Invite_Title}"><img src="images/newmail.png"/></a>
    </th>
    <th>{ally_name}</th>
    <th>{planet_name} <a href="galaxy.php?mode=3&galaxy={galaxy}&system={system}&planet={planet}">[{coordinated}]</a></th>
    <th>{position}</th>
</tr>
