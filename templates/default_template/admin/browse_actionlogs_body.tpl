<script>
    var Locale = new Array('{ExpandArrays}','{CollapseArrays}','{ExpandAmps}','{CollapseAmps}');
    var AutoExpandArray = {AutoExpandArray};
    var AutoExpandAmp = {AutoExpandAmp};
</script>
<script src="../dist/js/admin/browse_actionlogs_body.cachebuster-1546739003831.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="../dist/css/admin/browse_actionlogs_body.cachebuster-1546567692327.min.css" />
<br />
<form action="{ThisPage}" method="post" style="margin: 0px;" id="thisForm">
    <input type="hidden" name="filter" value="{SetFiltering}"/>
    <table width="900">
        <tbody>
            <tr>
                <td class="c pad" colspan="{TableColspan}">
                    <span style="float: left;">{LogBrowser} &#187; <a href="?uid={UID}">{User}: {UserName}</a>{CurrentBrowsingDate}</span>
                    <span style="float: right; color: lime; {FilteringDisplay}">({FilteringActive})</span>
                </td>
            </tr>
            {Headers}
            {Content}
            {Pagination}
        </tbody>
    </table>
</form>
