<tr class="queueinv">
    <td colspan="2">&nbsp;</td>
</tr>
<tr>
    <th
        class="pad2 w20x"
        rowspan="2"
    >
        {Data_ElementNo}
    </th>
    <th class="pad2">
        <a href="infos.php?gid={Data_ElementID}">
            {Data_Name}
        </a>
        ({Lang_LevelText} {Data_Level}, <b class="{Data_ModeColor}">{Data_ModeText}</b>)
        <br />
        <b
            class="lime endDate"
            title="<center>{Lang_EndTitleBeg} {Data_EndDateExpand}<br/>{Lang_EndTitleHour} {Data_EndTimeExpand}<br/>({Data_BuildTimeLabel}: {Data_BuildTime})</center>"
        >
            {Data_EndDate}
        </b>
    </th>
</tr>
<tr>
    <th
        class="pad2"
        colspan="2"
    >
        <a href="{Data_RemoveElementFromQueueLinkHref}">
            {Data_CancelBtn_Text}
        </a>
    </th>
</tr>
