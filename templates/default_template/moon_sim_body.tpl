<br />
<form action="" method="post">
    <input type="hidden" name="simulate" value="yes"/>
    <table style="width: 600px;">
        <tr>
            <td colspan="2" class="c">{Title}</td>
        </tr>
        <tr>
            <th style="width: 200px;" class="pad5">{Table1_Diameter}</th>
            <th style="width: 400px;" class="pad5">
                <input type="text" name="diameter" maxlength="4" style="width: 150px; padding: 3px;" value="{Input_RawDiameter}"/>
            </th>
        </tr>
        <tr>
            <th class="pad5">{Table1_ShipCount}</th>
            <th class="pad5">
                <input type="text" name="ship_count" style="width: 150px; padding: 3px;" value="{Input_RawShipCount}"/>
            </th>
        </tr>
        <tr>
            <th colspan="2" style="padding: 2px;"><input type="submit" value="{Table1_Simulate}" style="padding: 4px; font-weight: 700;"/></th>
        </tr>
        <tr style="visibility: hidden;"><td style="font-size: 5px;">&nbsp;</td></tr>
        <tbody {HideResult}>
            <tr>
                <td colspan="2" class="c">{Table2_Result}</td>
            </tr>
            <tr>
                <th colspan="2" class="pad5">
                    <span style="display: inline-block; float: left; text-align: right; width: 49%;">
                        {Table2_Diameter}:<br />{Table1_ShipCount}:<br /><br />{Table2_MoonChance}:<br />{Table2_FleetChance}:
                    </span>
                    <span style="display: inline-block; float: right; text-align: left; width: 49%;">
                        {Input_Diamter} {Table2_Km}<br />{Input_ShipCount}<br /><br />{Input_MoonChance}%<br />{Input_FleetChance}%
                    </span>
                </th>
            </tr>
        </tbody>
        <tbody {HideError}>
            <tr>
                <th colspan="2" class="c red pad5">{Input_Error}</th>
            </tr>
        </tbody>
    </table>
</form>
