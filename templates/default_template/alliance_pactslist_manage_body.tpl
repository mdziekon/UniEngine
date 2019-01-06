<style>
.NewPact {
    background: url('images/chglog_add.png') no-repeat 0pt 0pt;
    padding-left: 18px;
}
.hide {
    display: none;
}

.bigFnt {
    font-size: 13px;
}
.tLeft {
    text-align: left;
    padding-left: 5px !important;
}

.rollback, .remove, .stopchange, .accept, .refuse, .change {
    padding-left: 16px;
}
.rollback {
    background: url('./images/ban.png') no-repeat 0px 0px;
}
.remove {
    background: url('./images/delete.png') no-repeat 2px 2px;
}
.stopchange {
    background: url('./images/bin.png') no-repeat 0px 0px;
}
.accept {
    background: url('./images/tick.green.png') no-repeat 0px 0px;
}
.refuse {
    background: url('./images/tick.red.png') no-repeat 0px 0px;
}
.change {
    background: url('./images/edit.png') no-repeat 0px 0px;
}
</style>
<script>
var JSLang = {'tips': {'rollback': '{AWNP_Actions_rollback}', 'remove': '{AWNP_Actions_remove}', 'stopchange': '{AWNP_Actions_stopchange}', 'accept': '{AWNP_Actions_accept}', 'refuse': '{AWNP_Actions_refuse}', 'change': '{AWNP_Actions_change}'}};
$(document).ready(function()
{
    var TipStyle = {style: {classes: 'tiptip_content'}, delay: 0, position: {my: 'top center', 'at': 'bottom center', adjust: {y: 5}}};
    $('.rollback').qtip($.extend(TipStyle, {content: JSLang['tips']['rollback']}));
    $('.remove').qtip($.extend(TipStyle, {content: JSLang['tips']['remove']}));
    $('.stopchange').qtip($.extend(TipStyle, {content: JSLang['tips']['stopchange']}));
    $('.accept').qtip($.extend(TipStyle, {content: JSLang['tips']['accept']}));
    $('.refuse').qtip($.extend(TipStyle, {content: JSLang['tips']['refuse']}));
    $('.change').qtip($.extend(TipStyle, {content: JSLang['tips']['change']}));
});
</script>
<br/>
<table width="750">
    {Insert_MsgBox}
    <tr>
        <td class="c" colspan="5"><b class="fl">{AWNP_Pacts}</b><b class="fr">(<a href="alliance.php">&#171; {GoBack}</a>)</b></th>
    </tr>
    <tr>
        <th style="width: 200px;">{AWNP_AllyName}</th>
        <th style="width: 200px;">{AWNP_Date}</th>
        <th style="width: 100px;">{AWNP_Type}</th>
        <th style="width: 200px;">{AWNP_Status}</th>
        <th style="width: 75px;">{AWNP_Actions}</th>
    </tr>
    {ShowPactsList}
    <tbody {Insert_HideNewPact}>
        <tr><td class="c inv">&nbsp;</td></tr>
        <tr>
            <th colspan="5" class="pad5"><a href="?mode=newpact" class="NewPact fl">{AWNP_NewPact}</a>
        </tr>
    </tbody>
</table>
