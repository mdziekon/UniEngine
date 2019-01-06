<style>
.fr {
    float: right;
}
.pad2 {
    padding: 2px !important;
}
.pad4 {
    padding: 4px !important;
}
.hide {
    display: none;
}
.inv {
    visibility: hidden;
}
.h10 {
    height: 10px !important;
}
.w188 {
    width: 188px;
}
.w50 {
    width: 50px;
}
.w280 {
    width: 280px;
}
.w422 {
    width: 422px;
}
.yellow {
    color: yellow;
}
.expand {
    background: url('../images/expand.png') no-repeat 0pt 0pt;
}
.collapse {
    background: url('../images/collapse.png') no-repeat 0pt 0pt;
}
.expand, .collapse {
    padding-left: 18px;
    cursor: pointer;
}
.lalign {
    text-align: left;
}
</style>
<script>
$(document).ready(function(){
    $('th').addClass('pad2');

    $('.collapsed').hide();
    $('.ceswitch').click(function()
    {
        if($(this).hasClass('collapse')){
            $('.'+$(this).attr('id')+':not(.hide)').hide();
            $(this).removeClass('collapse');
            $(this).addClass('expand');
        } else if($(this).hasClass('expand')){
            $('.'+$(this).attr('id')+':not(.hide)').show().removeClass('collapsed');
            $(this).removeClass('expand');
            $(this).addClass('collapse');
        }
    }).tipTip({maxWidth: 'auto', content: '{Table2_CollExp}', defaultPosition: 'right', delay: 500, edgeOffset: 8});
});
</script>
<br />
<table width="752">
    <tr>
        <td colspan="4" class="c">{Table2_Header}<b class="fr">(<a href="?">{Table2_GoBack}</a>)</b></td>
    </tr>
    <tr>
        <th class="w188">{Table2_Username}</th>
        <th class="w188">{PHP_Username}</th>
        <th class="w188">{Table2_UID}</th>
        <th class="w188">{PHP_UID}</th>
    </tr>
    <tr{PHP_HideBreakError}>
        <th colspan="4" class="{PHP_BreakErrorColor}">{PHP_BreakErrorText}</th>
    </tr>
    <tbody{PHP_HideScanResult}>
        <tr>
            <th colspan="4" class="h10"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9sHHQ4rBaj6MfIAAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAAADUlEQVQI12NgYGBgAAAABQABXvMqOgAAAABJRU5ErkJggg==" /></th>
        </tr>
        <tr>
            <th>{Table2_ScanedLogs}</th>
            <th>{PHP_ScanedLogs}</th>
            <th>{Table2_ScanTime}</th>
            <th>{PHP_ScanTime}</th>
        </tr>
        <tr>
            <th>{Table2_DumpDate}</th>
            <th>{PHP_DumpDate}</th>
            <th>{Table2_DateDifference}</th>
            <th>{PHP_DateDifference}</th>
        </tr>
        <tr>
            <th colspan="4" class="{PHP_OverallResultColor}">{PHP_OverallResultText}</th>
        </tr>
    </tbody>
</table>
<table width="752"{PHP_HideScanResult}>
    <tr{PHP_HideFoundSummarys}>
        <td colspan="3" class="c"><b class="ceswitch collapse" id="ModuleNo04">&nbsp;</b>{Table2_SummaryResult} ({Table2_FinalSummaryCount})</td>
    </tr>
    <tr class="ModuleNo04 {PHP_HideFoundSummarys2}">
        <th class="w50">{Table2_OccurrenceIndex}</th>
        <th class="w280">{Table2_OccurrenceText}</th>
        <th class="w422">{Table2_OccurrenceData}</th>
    </tr>
    {PHP_AllFoundSummarys}
    <tr class="inv {PHP_HideFoundSummarys2}">
        <th>&nbsp;</th>
    </tr>

    <tr{PHP_HideFoundFatals}>
        <td colspan="3" class="c"><b class="ceswitch collapse" id="ModuleNo01">&nbsp;</b>{Table2_FoundFatals} ({Table2_FinalFatalCount})</td>
    </tr>
    <tr class="ModuleNo01 {PHP_HideFoundFatals2}">
        <th class="w50">{Table2_OccurrenceIndex}</th>
        <th class="w280">{Table2_OccurrenceText}</th>
        <th class="w422">{Table2_OccurrenceData}</th>
    </tr>
    {PHP_AllFoundFatals}
    <tr{PHP_HideFoundWarnings}>
        <td colspan="3" class="c"><b class="ceswitch collapse" id="ModuleNo02">&nbsp;</b>{Table2_FoundWarnings} ({Table2_FinalWarningCount})</td>
    </tr>
    <tr class="ModuleNo02 {PHP_HideFoundWarnings2}">
        <th class="w50">{Table2_OccurrenceIndex}</th>
        <th class="w280">{Table2_OccurrenceText}</th>
        <th class="w422">{Table2_OccurrenceData}</th>
    </tr>
    {PHP_AllFoundWarnings}
    <tr{PHP_HideFoundNotices}>
        <td colspan="3" class="c"><b class="ceswitch expand" id="ModuleNo03">&nbsp;</b>{Table2_FoundNotices} ({Table2_FinalNoticeCount})</td>
    </tr>
    <tr class="ModuleNo03 collapsed {PHP_HideFoundNotices2}">
        <th class="w50">{Table2_OccurrenceIndex}</th>
        <th class="w280">{Table2_OccurrenceText}</th>
        <th class="w422">{Table2_OccurrenceData}</th>
    </tr>
    {PHP_AllFoundNotices}
</table>
