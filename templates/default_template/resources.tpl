<script>
$(document).ready(function()
{
    $('.TotalUsage').change(function()
    {
        var SelectedVal = $(this).val();
        if(SelectedVal != '-'){
            $('.usageSel').each(function()
            {
                $(this).val(SelectedVal);
            });
        }
    }).keyup(function()
    {
        $(this).change();
    });
});
</script>
<br/>
<form action="" method="post">
    <table width="750">
        <tbody>
            <tr>
                <td class="c" colspan="6">{Production_of_resources_in_the_planet}</td>
            </tr>
            <tr>
                <th height="22" colspan="6" style="padding: 5px;">{Production_level_t}: <b class="{production_level_barcolor}">{production_level}</b><br /><b style="color: #87CEEB;">{Res_GeologistBonus}: +{GeologistBonusPercent}%</b></th>
            </tr>
            <tr>
                <th height="22">&nbsp;</th>
                <th width="80">{Metal}</th>
                <th width="80">{Crystal}</th>
                <th width="80">{Deuterium}</th>
                <th width="80">{Energy}</th>
                <th width="80">{MineUsage}</th>
            </tr>
            <tr>
                <th height="22">{Basic_income}</th>
                <th><b>{metal_basic_income}</b></th>
                <th><b>{crystal_basic_income}</b></th>
                <th><b>{deuterium_basic_income}</b></th>
                <th><b>{energy_basic_income}</b></th>
                <th><b>-</b></th>
            </tr>
            <tr>
                <th colspan="6" style="height: 5px; font-size: 1%;">&nbsp;</th>
            </tr>
            {resource_row}
            <tr>
                <th colspan="6" style="height: 5px; font-size: 1%;">&nbsp;</th>
            </tr>
            <tr>
                <th height="22">{Stores_capacity}</th>
                <th>{metal_max}</th>
                <th>{crystal_max}</th>
                <th>{deuterium_max}</th>
                <th><b>-</b></th>
                <th><b>-</b></th>
            </tr>
            <tr>
                <th height="22">{Total}:</th>
                <th><b>{metal_total}</b></th>
                <th><b>{crystal_total}</b></th>
                <th><b>{deuterium_total}</b></th>
                <th><b>{energy_total}</b></th>
                <th>
                    <select style="font-weight: bold; width: 60px;" class="TotalUsage">
                        <option value="-">-</option>
                        <option value="100">100%</option>
                        <option value="90">90%</option>
                        <option value="80">80%</option>
                        <option value="70">70%</option>
                        <option value="60">60%</option>
                        <option value="50">50%</option>
                        <option value="40">40%</option>
                        <option value="30">30%</option>
                        <option value="20">20%</option>
                        <option value="10">10%</option>
                        <option value="0">0%</option>
                    </select>
                </th>
            </tr>
            <tr>
                <th colspan="6" style="height: 5px; font-size: 1%;">&nbsp;</th>
            </tr>
            <tr>
                <th colspan="5" height="24"><input type="checkbox" name="setUsage2All" id="su2a"/> <label for="su2a" class="orange">{Res_SetThisUsage2All}</label></th>
                <th><input name="action" value="{Calcule}" type="submit" style="font-weight: bold;"/></th>
            </tr>
        </tbody>
    </table>
</form>
<br/>
<table width="750">
    <tbody>
        <tr>
            <td class="c" colspan="4">{Widespread_production}</td>
        </tr><tr>
            <th>&nbsp;</th>
            <th>{Daily}</th>
            <th>{Weekly}</th>
            <th>{Monthly}</th>
        </tr><tr>
            <th>{Metal}</th>
            <th>{daily_metal}</th>
            <th>{weekly_metal}</th>
            <th>{monthly_metal}</th>
        </tr><tr>
            <th>{Crystal}</th>
            <th>{daily_crystal}</th>
            <th>{weekly_crystal}</th>
            <th>{monthly_crystal}</th>
        </tr><tr>
            <th>{Deuterium}</th>
            <th>{daily_deuterium}</th>
            <th>{weekly_deuterium}</th>
            <th>{monthly_deuterium}</th>
        </tr>
    </tbody>
</table>
<br/>
<table width="750">
    <tbody>
        <tr>
            <td class="c" colspan="4">{NeedenTransporters} {planet_type_res} {nazwa}</td>
        </tr>
        <tr>
            <th>&nbsp;</th>
            <th>{Needen}</th>
            <th>{YouHave}</th>
            <th>{Missing}</th>
        </tr><tr>
            <th>{small_cargo_name}</th>
            <th>{trans_s}</th>
            <th>{have_s}</th>
            <th>{missing_s}</th>
        </tr><tr>
            <th>{big_cargo_name}</th>
            <th>{trans_b}</th>
            <th>{have_b}</th>
            <th>{missing_b}</th>
        </tr><tr>
            <th>{mega_cargo_name}</th>
            <th>{trans_m}</th>
            <th>{have_m}</th>
            <th>{missing_m}</th>
        </tr>
    </tbody>
</table>
<br/>
<table width="750">
    <tbody>
        <tr>
            <td class="c" colspan="3">{Storage_state}</td>
        </tr><tr>
            <th>{Metal}</th>
            <th>{metal_storage}</th>
            <th width="250">
                <div style="border: 1px solid rgb(153, 153, 255); width: 250px;">
                <div id="AlmMBar" style="background-color: {metal_storage_barcolor}; width: {metal_storage_bar}px;">
                &nbsp;
                </div>
                </div>
            </th>
        </tr><tr>
            <th>{Crystal}</th>
            <th>{crystal_storage}</th>
            <th width="250">
                <div style="border: 1px solid rgb(153, 153, 255); width: 250px;">
                <div id="AlmCBar" style="background-color: {crystal_storage_barcolor}; width: {crystal_storage_bar}px;">
                &nbsp;
                </div>
                </div>
            </th>
        </tr><tr>
            <th>{Deuterium}</th>
            <th>{deuterium_storage}</th>
            <th width="250">
                <div style="border: 1px solid rgb(153, 153, 255); width: 250px;">
                <div id="AlmDBar" style="background-color: {deuterium_storage_barcolor}; width: {deuterium_storage_bar}px;">
                &nbsp;
                </div>
                </div>
            </th>
        </tr>
    </tbody>
</table>
<br/>
