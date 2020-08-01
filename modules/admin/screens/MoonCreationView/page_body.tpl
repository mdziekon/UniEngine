<style>
.fInput
{
    width: 90%;
    padding: 3px;
}
</style>
<br/>
<form action="addMoon.php" method="post">
    <input type="hidden" name="sent" value="1">
    <table width="600">
        <tr>
            <td class="c" colspan="2">{AddMoon_Title}</td>
        </tr>
        <tr>
            <th width="300">{AddMoon_PlanetID}</th>
            <th><input type="text" name="planetID" class="fInput" value="{PHP_Ins_PlanetID}"></th>
        </tr>
        <tr>
            <th>{AddMoon_Name}</th>
            <th><input type="text" name="name" class="fInput" value="{PHP_Ins_Name}"></th>
        </tr>
        <tr>
            <th>{AddMoon_Diameter}</th>
            <th><input type="text" name="diameter" class="fInput" value="{PHP_Ins_Diameter}"></th>
        </tr>
        <tr>
            <th colspan="2"><input type="submit" value="{AddMoon_Submit}" class="fInput" style="font-weight: bold;"></th>
        </tr>
        <tr style="visibility: hidden;">
            <th>&nbsp;</th>
        </tr>
        <tr style="{PHP_InfoBox_Hide}">
            <th colspan="2" class="pad5 {PHP_InfoBox_Color}">{PHP_InfoBox_Text}</th>
        </tr>
    </table>
</form>
