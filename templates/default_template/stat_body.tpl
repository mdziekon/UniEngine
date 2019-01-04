<script>
var JSVars = {'LastWhoVal': '{LastWhoVal}', 'QuickChangeInfo': '{QuickChangeInfo}', 'DailyChangeInfo': '{DailyChangeInfo}'};
</script>
<link rel="stylesheet" type="text/css" href="dist/css/stats.cachebuster-1546564327123.min.css" />
<script src="dist/js/stats.cachebuster-1545956361123.min.js"/></script>
<br/>
<form method="post" id="statForm">
    <table width="700">
        <tr>
            <td class="c center">{stat_header_txt} {stat_date}</td>
        </tr>
        <tr>
            <th align="center">
                <table style="width: 100%;">
                    <tr class="pad2">
                        <th style="width: 6%; border: none;">&nbsp;</th>
                        <th width="14%">{stat_show}</th>
                        <th width="14%"><select name="who">{who}</select></th>
                        <th width="14%">{stat_by}</th>
                        <th width="14%"><select name="type">{type}</select></th>
                        <th width="14%">{stat_range}</th>
                        <th width="18%">
                            <span style="{HideRangeSelector}">
                                <input type="button" id="prev" value="&#171;" style="font-weight: 700; width: 15px;"/>
                                <select name="range" id="range">{range}</select>
                                <input type="button" id="next" value="&#187;" style="font-weight: 700; width: 15px;"/>
                            </span>
                            <span style="{HideNoRangeSelector}">
                                <b>1 - {MaxPlace}</b>
                            </span>
                        </th>
                        <th style="width: 6%; border: none;">&nbsp;</th>
                    <tr>
                </table>
            </th>
        </tr>
    </table>
</form>
<table width="700">
    {stat_header}
    {stat_values}
</table>
