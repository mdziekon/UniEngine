<tr>
    <th>{i}</th>
    <th><a href="profile.php?uid={id}">{username}</a></th>
    <th>
        <a href="messages.php?mode=write&uid={id}">
            <img src="{skinpath}img/m.gif" border="0" alt="{write}"/>
        </a>
    </th>
    <th>{rank}</th>
    <th>{points}</th>
    <th>
        [<a href="galaxy.php?mode=3&galaxy={galaxy}&system={system}&planet={planet}">{galaxy}:{system}:{planet}</a>]
    </th>
    <th>{reg_time}</th>
    <th>
        <b class="{onlinecolor}">{onlinetime}</b>
    </th>
</tr>
