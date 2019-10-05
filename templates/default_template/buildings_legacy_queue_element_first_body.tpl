<tr>
    <td class="l" colspan="2">
        <b>
            {Data_ListID}. {Data_ElementName}
            ({Lang_Level} {Data_ElementLevel})
            <span class="{Data_HideIsDowngradeLabelClass}">
                ({Lang_DowngradeLabel})
            </span>
        </b>
    </td>
    <td class="k">
        <div id="blc" class="z">{Data_BuildTimeSecondsLeft}</div>
        <div id="dlink">
            <a
                href="buildings.php?listid={Data_ListID}&amp;cmd=cancel&amp;planet={Data_PlanetID}"
                class="queue_element_cancel_first {Data_ElementCancellableClass}"
            >
                {Lang_DeleteFirstElement}
            </a>
        </div>

        <br/>
        <b class="lime">
            {Data_ElementProgressEndTimeDatepoint}
        </b>

        <script>
            pp = "{Data_BuildTimeSecondsLeft}";
            pk = "{Data_ListID}";
            pm = "cancel";
            pl = "{Data_PlanetID}";

            t();
        </script>
    </td>
</tr>
