<style>
.NewPact {
    background: url('images/chglog_add.png') no-repeat 0pt 0pt;
    padding-left: 18px;
}
.hide {
    display: none;
}
.tLeft {
    text-align: left;
    padding-left: 5px !important;
}
</style>
<script>
$(document).ready(function()
{

});
</script>
<br/>
<table width="750">
    <tr>
        <td class="c" colspan="4"><b class="fl">{AWNP_Pacts}</b><b class="fr">(<a href="alliance.php">&#171; {GoBack}</a>)</b></th>
    </tr>
    <tr>
        <th style="width: 200px;">{AWNP_AllyName}</th>
        <th style="width: 200px;">{AWNP_Date}</th>
        <th style="width: 100px;">{AWNP_Type}</th>
        <th style="width: 200px;">{AWNP_Status}</th>
    </tr>
    {ShowPactsList}
    <tbody {Insert_HideNewPact}>
        <tr><td class="c inv">&nbsp;</td></tr>
        <tr>
            <th colspan="4" class="pad5"><a href="?mode=newpact" class="NewPact fl">{AWNP_NewPact}</a>
        </tr>
    </tbody>
</table>
