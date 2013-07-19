<tr style="display: none;">
    <th width="30">16</th>
    <th colspan="10">
        <a href="fleet.php?galaxy={Galaxy}&amp;system={System}&amp;planet=16;planettype=1&amp;target_mission=15">{Footer_Expedition}</a>
    </th>
</tr>
<tr>
    <td class="c" colspan="7">({Footer_ColonizedPlanets}: <span id="colonizedCount">{Input_ColonizedPlanets}</span>)</td>
    <td class="c" colspan="3">{Input_LegendPopup}</td>
</tr>
<tr>
    <td class="c center" colspan="3">
        {Footer_Missiles}: <span id="missiles">{Input_Missiles}</span>
        <span style="{Input_Missiles_NoDisplay}"><br />(<a class="orange" href="infos.php?gid=44" title="{Footer_MissilesTitle}">{Footer_MissilesDestroy}</a>)</span>
    </td>
    <td class="c center" colspan="4" style="line-height: 16px;">
        <u>{Footer_Ships}</u>:<br/>
        <span class="fl_l">{Footer_Ship_Recyclers}:</span> <span class="fl_r" id="recyclers">{Input_Recyclers}</span><br/>
        <span class="fl_l">{Footer_Ship_SpyProbes}:</span> <span class="fl_r" id="probes">{Input_SpyProbes}</span><br/>
        <span class="fl_l">{Footer_Ship_Colonizators}:</span> <span class="fl_r" id="colonizers">{Input_Colonizers}</span><br/>
    </td>
    <td class="c center" colspan="3">
        {Footer_FlyingFleets}: <span id="slots">{Input_FlyingFleets}</span>/{Input_MaxFleets}
    </td>
</tr>