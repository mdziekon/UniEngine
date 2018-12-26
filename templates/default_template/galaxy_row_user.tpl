<th class="avWid hiFnt">{Avatar}</th>
<th class="nowrap">
    <a class="point" onmouseover='return overlib("<table width=190><tr><td class=c colspan=2>{Lang_User}: {Username}</td></tr><tr><th><a href=profile.php?uid={UserID}>{Lang_Profile}</a></th></tr><tr{Hide_Message}><th><a href=messages.php?mode=write&uid={UserID}>{Lang_Message}</a></th></tr><tr{Hide_Buddy}><th><a href=buddy.php?cmd=add&uid={UserID}>{Lang_Buddy}</a></th></tr><tr{Hide_InviteToAlly}><th><a href=alliance.php?mode=invite&uid={UserID}>{Lang_AllyInvite}</a></th></tr><tr><th><a href=stats.php?who=player&start={StatStart}>{Lang_Stats} ({Position})</a></th></tr>{Insert_MoraleBox}</table>", STICKY, MOUSEOFF, DELAY, 500, CENTER, OFFSETX, -40, OFFSETY, -40 );' onmouseout="return nd();">
        <b class="{NameClass}">{Username}</b> {AddOldUsername} {Statuses}
    </a>
</th>
<th {Add_hiFntClass}>{PositionTH}</th>
