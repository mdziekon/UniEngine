<tr>
    <td colspan="4" class="c" style="padding: 5px;">
        {ResultTitle} (<a href="#" id="toggleView_Result">{ShowHide}</a>)
    </td>
</tr>
<tr id="result">
    <td colspan="2" class="c" style="padding: 5px;">
    {See_last_BR}<a style="cursor: pointer;" onclick="f('battlereport.php?id={id}&sim=1', '');"><span style="color: orange"><u>{sys_mess_attack_report}</u></span></a>
    {ShowAllBR}
    <br/><br/>
    {CalcTotalTime}: {time}<br/><br />
    {BattleWonBy}: <span class="{Winner_Color}">{Winner_Name}</span><br /><br />
    {txt_rounds}: {rounds}
    </td>
    <td colspan="2" class="c" style="padding: 5px;">
    {AddInfo}
    <u>{avg_atk_lost}:</u><br />{total_lost_atk_met} {units} {Metal_rec},<br />{total_lost_atk_cry} {units} {Crystal_rec},<br />{total_lost_atk_deu} {units} {Deuterium_rec}<br/>
    ({ship_lost_atk} {ships})<br/><br />
    <u>{avg_def_lost}:</u><br />{total_lost_def_met} {units} {Metal_rec},<br />{total_lost_def_cry} {units} {Crystal_rec},<br />{total_lost_def_deu} {units} {Deuterium_rec}<br/>
    ({ship_lost_def} {ships})
    </td>
</tr>
