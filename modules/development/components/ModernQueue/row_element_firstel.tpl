{PHPInject_ChronoAppletScriptCode}
<tr>
    <th
        class="pad2"
        style="font-size: 15px;"
        id="bxxQueueFirstTimer"
        colspan="2"
    >
        {Data_EndTimer}
    </th>
</tr>
<tr>
    <th
        class="pad2"
        colspan="2"
    >
        <table class="w100p">
            <tr>
                <td
                    valign="top"
                    style="width: 70px;"
                >
                    <a href="infos.php?gid={Data_ElementID}">
                        <img
                            src="{Data_SkinPath}gebaeude/{Data_ElementID}.gif"
                            width="64"
                            height="64"
                            class="buildImg"
                        />
                    </a>
                </td>
                <td
                    style="text-align: left;"
                    valign="top"
                >
                    <a href="infos.php?gid={Data_ElementID}">
                        <b>{Data_Name}</b>
                    </a>
                    <br />
                    <b>
                        {Lang_LevelText} {Data_Level},
                    </b>
                    <b class="{Data_ModeColor}">
                        {Data_ModeText}
                    </b>
                    <br /><br />
                    <b>
                        {Lang_EndText}:
                    </b>
                    <b
                        class="lime endDate"
                        title="<center>{Lang_EndTitleBeg} {Data_EndDateExpand}<br/>{Lang_EndTitleHour} {Data_EndTimeExpand}</center>"
                    >
                        {Data_EndDate}
                    </b>
                </td>
            </tr>
        </table>
    </th>
</tr>
<tr>
    <th
        class="pad2"
        colspan="2"
    >
        <a
            id="QueueCancel"
            href="{Data_RemoveElementFromQueueLinkHref}"
            class="cancelQueue {Data_CancelLock_class}"
        >
            {Data_CancelBtn_Text}
        </a>
    </th>
</tr>
