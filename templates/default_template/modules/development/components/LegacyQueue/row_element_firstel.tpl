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
        {PHPInject_ChronoAppletScriptCode}

        <div id="bxxQueueFirstTimer" class="z">
            {Data_BuildTimeEndFormatted}
        </div>
        <div>
            <a
                id="QueueCancel"
                href="{Data_RemoveElementFromQueueLinkHref}"
                class="cancelQueue {Data_ElementCancellableClass}"
            >
                {Lang_DeleteFirstElement}
            </a>
        </div>

        <br/>
        <b class="lime">
            {Data_ElementProgressEndTimeDatepoint}
        </b>
    </td>
</tr>
