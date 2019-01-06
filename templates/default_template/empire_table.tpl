<style>
.pad2 {
    padding: 2px;
}
.fmin {
    font-size: 9px;
}
.fmin2 {
    font-size: 10px;
}
.fr {
    float: right;
}
.Hover {
    background-color: #455B87;
    border-color: #526EA3;
}
.Select {
    background-color: #4E6797;
    border-color: #607BAF;
}
.plImg {
    border: 2px solid black;
    width: 48px;
    height: 48px;
}
.w75x {
    width: 75px;
}
</style>
<script>
$(document).ready(function()
{
    $("[class^='addhover']").hover(
    function()
    {
        var getClasses = $(this).attr('class').split(' ');
        $('.'+getClasses[0]).addClass('Hover');
    },
    function()
    {
        var getClasses = $(this).attr('class').split(' ');
        $('.'+getClasses[0]).removeClass('Hover');
    });
});
</script>
<br/>
<table border="0" style="min-width: 350px;" align="left">
<tr style="height: 20px;" valign="left">
    <td class="c" colspan="{PlCount}">{empire_vision}<span{HideMoons}> (<a href="empire.php">{ShowPlanets}</a> | <a href="empire.php?type=3">{ShowMoons}</a>)</span></td>
</tr>
<tr>
    <th width="75">&nbsp;</th>
    {row_img}
</tr>
<tr>
    <th width="75" class="fmin">{name}</th>
    {row_name}
</tr>
<tr>
    <th width="75" class="fmin">{coordinates}</th>
    {row_coords}
</tr>
<tr>
    <th width="75" class="fmin">{fields}</th>
    {row_fields}
</tr>
<tr>
    <td class="c" colspan="{PlCount}" align="left">{resources}</td>
</tr>
<tr>
    <th width="75" class="fmin">{metal}</th>
    {row_metal}
</tr>
<tr>
    <th width="75" class="fmin">{crystal}</th>
    {row_crystal}
</tr>
<tr>
    <th width="75" class="fmin">{deuterium}</th>
    {row_deuterium}
</tr>
<tr>
    <th width="75" class="fmin">{energy}</th>
    {row_energy}
</tr>
<tr>
    <td class="c" colspan="{PlCount}" align="left">{buildings}</td>
</tr>
    {row_buildings}
<tr>
    <td class="c" colspan="{PlCount}" align="left">{ships}</td>
</tr>
    {row_ships}
<tr>
    <td class="c" colspan="{PlCount}" align="left">{defense}</td>
</tr>
    {row_defense}
</table>
