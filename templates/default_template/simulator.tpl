<script>
var CurrentSlot = 1;
{fill_with_mytechs}
{fill_with_myfleets}
var PlanetOwnerTxt = '{PlanetOwner}';
var AllowPrettyInputBox = {AllowPrettyInputBox};
</script>
<script src="dist/js/_libCommon.cachebuster-1649555016585.min.js"></script>
<script src="./dist/js/simulator.cachebuster-1649640805832.min.js"></script>
<link rel="stylesheet" type="text/css" href="dist/css/simulator.cachebuster-1546564327123.min.css" />
<br />
<form id="simForm" action="" method="post">
    <input type="hidden" name="simulate" value="yes"/>
    <table width="800">
        {SimResult}
        {BreakLineLight}
        {BreakLineLight}
        <tr>
            <th colspan="4" class="pad5">
                <input type="submit" class="button" style="font-weight: 700;" value="{Submit}"/>
            </th>
        </tr>
        {BreakLineLight}
        {BreakLineLight}
        <tr>
            <td colspan="4" class="c">
                {SelectSlot}
            </td>
        </tr>
        <tr>
            <th colspan="4" class="pad5">
                <input type="button" value="#1" class="chgSlot bold"/>
                <input type="button" value="#2" class="chgSlot"/>
                <input type="button" value="#3" class="chgSlot"/>
                <input type="button" value="#4" class="chgSlot"/>
                <input type="button" value="#5" class="chgSlot"/>
                <input type="button" value="#6" class="chgSlot"/>
                <input type="button" value="#7" class="chgSlot"/>
                <input type="button" value="#8" class="chgSlot"/>
                <input type="button" value="#9" class="chgSlot"/>
                <input type="button" value="#10" class="chgSlot"/>
                <input type="button" value="#11" class="chgSlot"/>
                <input type="button" value="#12" class="chgSlot"/>
                <input type="button" value="#13" class="chgSlot"/>
                <input type="button" value="#14" class="chgSlot"/>
                <input type="button" value="#15" class="chgSlot"/>
                <input type="button" value="#16" class="chgSlot"/>
            </th>
        </tr>
        {BreakLineLight}
        {BreakLineLight}
        <tr>
            <td class="c" style="width: 50%;" colspan="2">
                {Attacker}
            </td>
            <td class="c" style="width: 50%;" colspan="2">
                {Defender}
            </td>
        </tr>
        {rows}
        {BreakLineLight}
        {BreakLineLight}
        <tr>
            <td colspan="4" class="c">
                {SelectSlot}
            </td>
        </tr>
        <tr>
            <th colspan="4" class="pad5">
                <input type="button" value="#1" class="chgSlot bold"/>
                <input type="button" value="#2" class="chgSlot"/>
                <input type="button" value="#3" class="chgSlot"/>
                <input type="button" value="#4" class="chgSlot"/>
                <input type="button" value="#5" class="chgSlot"/>
                <input type="button" value="#6" class="chgSlot"/>
                <input type="button" value="#7" class="chgSlot"/>
                <input type="button" value="#8" class="chgSlot"/>
                <input type="button" value="#9" class="chgSlot"/>
                <input type="button" value="#10" class="chgSlot"/>
                <input type="button" value="#11" class="chgSlot"/>
                <input type="button" value="#12" class="chgSlot"/>
                <input type="button" value="#13" class="chgSlot"/>
                <input type="button" value="#14" class="chgSlot"/>
                <input type="button" value="#15" class="chgSlot"/>
                <input type="button" value="#16" class="chgSlot"/>
            </th>
        </tr>
        {BreakLineLight}
        {BreakLineLight}
        <tr>
            <th colspan="4" class="pad5">
                <input type="submit" class="button" style="font-weight: 700;" value="{Submit}"/>
            </th>
        </tr>
    </table>
</form>
