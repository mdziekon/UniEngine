<style>
.rf_l {
    display: inline-block;
    width: 80%;
    float: left;
    text-align: right;
}
.rf_r {
    display: inline-block;
    width: 19%;
    float: right;
    text-align: left;
}
.techDet:hover > th {
    background-color: #455B87;
    border-color: #526EA3;
}
</style>
<br />
<table width="600">
    <tr>
        <td class="c center" colspan="4">{element_typ}</td>
    </tr>
    <tr>
        <th class="pad2" colspan="4">{nfo_techinfoabout} - <b class="orange">{name}</b></th>
    </tr>
    <tr>
        <th colspan="4">
            <table>
                <tr>
                    <th style="border: 0px;" valign="top">
                        <img src="{skinpath}gebaeude/{image}.gif" height="120" width="120" style="border: 2px solid black;" />
                    </th>
                    <th style="border: 0px; text-align: left;">
                        {description}<br/><br/>
                        <span style="width: 100%;">{rf_info_to}{rf_info_fr}</span>
                    </th>
                </tr>
            </table>
        </th>
    </tr>
    <tr>
        <td class="c center pad2" style="width: 30%;">{nfo_techdetails_title}</td>
        <td class="c center pad2" style="width: 25%;">{nfo_techdetails_base}</td>
        <td class="c center pad2" style="width: 15%;">{nfo_techdetails_modifier}</td>
        <td class="c center pad2" style="width: 25%;">{nfo_techdetails_modified}</td>
    </tr>
    <tr class="techDet">
        <th>{nfo_struct_pt}</th>
        <th>{Insert_Hull_Base}</th>
        <th>+{Insert_Hull_Modifier}%</th>
        <th>{Insert_Hull_Modified}</th>
    </tr>
    <tr class="techDet">
        <th>{nfo_shielf_pt}</th>
        <th>{Insert_Shield_Base}</th>
        <th>+{Insert_Shield_Modifier}%</th>
        <th>{Insert_Shield_Modified}</th>
    </tr>
    <tr class="techDet">
        <th>{nfo_attack_type}</th>
        <th colspan="3">{Insert_WeaponType}</th>
    </tr>
    <tr class="techDet">
        <th>{nfo_attack_pt}</th>
        <th>{Insert_Force_Base}</th>
        <th>+{Insert_Force_Modifier}%</th>
        <th>{Insert_Force_Modified}</th>
    </tr>
    <tr class="techDet">
        <th>{nfo_capacity}</th>
        <th colspan="3">{Insert_Storage_Base} {nfo_units}</th>
    </tr>
    <tr class="techDet">
        <th>{nfo_base_speed}</th>
        <th>{Insert_Speed_Base}</th>
        <th>+{Insert_Speed_Modifier}%</th>
        <th>{Insert_Speed_Modified}</th>
    </tr>
    <tr class="techDet">
        <th>{nfo_consumption}</th>
        <th colspan="3">{Insert_Fuel_Base}</th>
    </tr>
</table>
