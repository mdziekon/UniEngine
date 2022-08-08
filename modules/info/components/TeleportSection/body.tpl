<script>
var AllowPrettyInputBox = {Insert_AllowPrettyInputBox};
</script>
<script src="dist/js/_libCommon.cachebuster-1649555016585.min.js"></script>
<script src="dist/js/infos_teleport.cachebuster-1649640625758.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="dist/css/infos_teleport.cachebuster-1546564327123.min.css" />
<br/>
{gate_time_script}
<form action="jumpgate.php" method="post">
    <input type="hidden" name="dojump" value="yes"/>
    <table width="600" class="tblNoMarg" {Gate_HideSelector}>
        <tr>
            <td class="c" colspan="2">{gate_selecttarget}</td>
        </tr>
        <tr>
            <th style="width: 50%;" class="pad5">{gate_start_moon}</th>
            <th style="width: 48%;" class="pad5">{gate_start_link}</th>
        </tr>
        <tr>
            <th>{gate_dest_moon}</th>
            <th>
                <select name="jumpto" class="jumpto">{gate_dest_moons}</select>
            </th>
        </tr>
        <tr>
            <th>{gate_changemoon}</th>
            <th><input type="checkbox" name="changemoon" checked/></th>
        </tr>
    </table>
    <table width="600" class="tblNoMarg" {Gate_HideInfoBox}>
        <tr>
            <th class="pad5 red" colspan="2">{gate_infobox}</th>
        </tr>
    </table>
    <table width="600" class="tblNoMarg" {Gate_HideShips}>
        <tbody>
        <tr>
            <td class="c" colspan="4">{gate_ship_sel}</td>
        </tr>
        <tr {Gate_HideNextJumpTimer}>
            <th colspan="4">{gate_wait_time}</th>
        </tr>
        <tr>
            <th style="width: 35%;" class="pad2">{gate_title_ship}</th>
            <th style="width: 20%;" class="pad2">{gate_title_count}</th>
            <th style="width: 20%;" class="pad2">{gate_title_input}</th>
            <th style="width: 15%;" class="pad2"><a style="cursor: pointer;" onclick="maxall();">{gate_everything}</a></th>
        </tr>
        {gate_fleet_rows}
        <tr>
            <th colspan="4" class="pad5">
                <input value="{gate_jump_btn}" class="{PHP_JumpGate_SubmitColor}" type="submit" id="formSubmit"/>
            </th>
        </tr>
        </tbody>
    </table>
</form>
