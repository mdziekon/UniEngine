<form id="MissileForm" {Input_HideMissileForm}>
    <input type="hidden" name="m_galaxy" value="{Galaxy}"/>
    <input type="hidden" name="m_system" value="{System}"/>
    <input type="hidden" name="m_planet" value="{Planet}"/>

    <table border="0" class="galWidth">
        <tr>
            <td class="c center" colspan="4"><span class="red">{MSelector_Title} [<b id="MissilePos">{ThisPos}</b>]</span> <b class="orange point" id="closeMF">({MSelector_Close})</b></td>
        </tr>
        <tr>
            <th class="c pad2" style="width: 25%;">{MSelector_AvailableM}:</th>
            <th class="c pad2" style="width: 25%;" id="missiles2">{Input_MissileCount}</th>
            <th class="c pad2" colspan="2">{MSelector_Target}:</th>
        </tr>
        <tr>
            <th class="c pad2">{MSelector_MCount}:</th>
            <th class="c pad2"><input type="text" name="m_count" class="pad2" style="width: 100px;" /></th>
            <th class="c pad2" colspan="2">
                <select name="m_target" class="pad2">
                    <option value="0" selected="selected">{MSelector_TargetAll}</option>
                    {Input_Targets}
                </select>
            </th>
        </tr>
        <tr>
            <td class="c center pad5" colspan="4">
                <input type="submit" class="red" style="width: 95%; font-weight: 700;" value="{MSelector_Submit}" id="sendMissiles"/>
            </td>
        </tr>
    </table>
</form>
