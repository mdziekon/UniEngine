<tr>
    <th class="c pad" width="70%">
        {From} <input class="spad" type="text" size="2" name="from_day" value="{FromDay}" maxlength="2"/> <input class="spad" type="text" size="2" name="from_mon" value="{FromMon}" maxlength="2"/>
        <input class="spad" type="text" size="3" name="from_yea" value="{FromYea}" maxlength="4" style="margin-right: 30px;"/>
        {To} <input class="spad" type="text" size="2" name="to_day" value="{ToDay}" maxlength="2"/> <input class="spad" type="text" size="2" name="to_mon" value="{ToMon}" maxlength="2"/>
        <input class="spad" type="text" size="3" name="to_yea" value="{ToYea}" maxlength="4"/>
        <span style="float: right; margin-right: 15px;">
            <input style="width: 65px;" class="spad doFilter" type="submit" value="{Submit}"/>
            <input style="width: 65px;" class="spad doCleanFilter" type="reset" value="{Reset}"/>
        </span>
    </th>
    <th class="c pad" width="30%">{SearchForLogs}</th>
</tr>
<tr>
    <th class="c pad" colspan="2">{TotalLogsCount}: {LogsCount}</th>
</tr>
<tr style="{NoHeader}">
    <th class="c pad" width="70%"><a style="color: orange;" href="?uid={UID}&amp;sort=date&amp;order={DateSort}">{Date}</a></th>
    <th class="c pad" width="30%"><a style="color: orange;" href="?uid={UID}&amp;sort=size&amp;order={SizeSort}">{Size}</a></th>
</tr>
