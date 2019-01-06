<style>
.inp {
    width: 90%;
    padding: 3px;
}
</style>
<script>
var JSLang = {'TypeDesc': {'1': '{Ally_PactNew_TypeDesc_1}', '2': '{Ally_PactNew_TypeDesc_2}', '3': '{Ally_PactNew_TypeDesc_3}', '4': '{Ally_PactNew_TypeDesc_4}'}};
$(document).ready(function()
{
    $('select[name="type"]')
    .change(function()
    {
        $('#typeDesc').html(JSLang['TypeDesc'][$(this).val()]);
    })
    .keyup(function()
    {
        $(this).change();
    });

    $('select[name="type"]').change();
});
</script>
<br/>
<form action="?mode=changepact&amp;aid={Insert_AID}" method="post">
    <input type="hidden" name="sent" value="1"/>
    <table style="width: 750px;">
        {Insert_MsgBox}
        <tr>
            <td class="c" colspan="2"><b class="fl">{Ally_PactChange_Body_Head}</b><b class="fr">(<a href="alliance.php?mode=pactslist">&#171; {GoBack}</a>)</b></td>
        </tr>
        <tr>
            <th style="width: 150px;">{Ally_PactChange_Body_Ally}</th>
            <th class="pad5">{Insert_AllyName}</th>
        </tr>
        <tr>
            <th>{Ally_PactNew_Body_Type}</th>
            <th class="pad2" style="height: 170px;" valign="top">
                {Ally_PactChange_Body_CurrentType}: {Insert_CurrentType}<br/><br/>
                <select name="type" class="inp">
                    <option value="1" {Insert_CurrentOption_1}>{Ally_PactNew_Type_1}</option>
                    <option value="2" {Insert_CurrentOption_2}>{Ally_PactNew_Type_2}</option>
                    <option value="3" {Insert_CurrentOption_3}>{Ally_PactNew_Type_3}</option>
                    <option value="4" {Insert_CurrentOption_4}>{Ally_PactNew_Type_4}</option>
                </select><br/><br/>
                <div id="typeDesc" class="pad5" style="text-align: left; font-weight: normal;"></div>
            </th>
        </tr>
        <tr>
            <th class="pad2" colspan="2"><input type="submit" value="{Ally_PactChange_Body_Submit}" class="pad2 lime" style="width: 200px; font-weight: 700;"/></th>
        </tr>
    </table>
</form>
