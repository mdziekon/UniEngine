<style>
.breakTR {
    visibility: hidden;
}
.smallLine {
    font-size: 10%;
}
.leftAl {
    text-align: left;
    padding: 5px;
}
.pad {
    padding: 5px;
}
</style>
<br/>
<form action="" method="post">
    <input type="hidden" name="simulate" value="yes"/>
    <table width="800">
        <tbody>
            {Result}
            <tr>
                <td class="c" colspan="4">{Title}</td>
            </tr>
            <tr>
                <th colspan="2" style="color: orange;">{Defender}</th>
                <th colspan="2" style="color: orange;">{Attacker}</th>
            </tr>
            <tr><th colspan="4" class="smallLine">&nbsp;</th></tr>
            <tr>
                <th class="leftAl" width="22%">{DefTech}:</th>
                <th class="leftAl" width="28%"><input type="text" name="def[tech]" value="{SetDefTech}"/></th>
                <th class="leftAl" width="22%">{AtkTech}:</th>
                <th class="leftAl" width="28%"><input type="text" name="atk[tech]" value="{SetAtkTech}"/></th>
            </tr>
            <tr><th colspan="4" class="smallLine">&nbsp;</th></tr>
            <tr>
                <th class="leftAl">{InterceptMiss}:</th>
                <th class="leftAl"><input type="text" name="def[502]" value="{SetICM}"/> {LostICM}</th>
                <th class="leftAl">{InterplanetMiss}:</th>
                <th class="leftAl"><input type="text" name="atk[503]" value="{SetIPM}"/></th>
            </tr>
            <tr><th colspan="4" class="smallLine">&nbsp;</th></tr>
            {DefenceRows}
            <tr class="breakTR"><th></th></tr>
            <tr class="breakTR"><th></th></tr>
            <tr>
                <th colspan="4" style="padding: 5px;">
                    <input type="submit" style="border: 1px solid #A1A1A1;" value="{Submit}"/>
                </th>
            </tr>
        </tbody>
    </table>
</form>
