<tr>
    <td class="c" colspan="6" id="darkenergy">{DarkEnergyTitle}</td>
</tr>
<tr>
    <th class="c pad deTab" id="deTab01">{SMS_TAB_1}</th>
    <th class="c pad deCont" rowspan="4" colspan="5">
        <span class="deForm01">{SMS_TEXT_1}<br />&nbsp;</span>
        <span class="deForm02">{SMS_TEXT_2}<br />&nbsp;</span>
        <span class="deForm03">{SMS_TEXT_3}<br />&nbsp;</span>
        <span class="deForm04">{SMS_TEXT_4}<br />&nbsp;</span>
        <br />
        <hr />
        {SMS_InputCode}
        <form action="?show=deform" method="post">
            <input type="hidden" name="mode" value="buyde" />
            <input type="hidden" name="option" value=""/>
            <input maxlength="12" class="deInputs" type="text" name="code" autocomplete="off"/><br />
            <input type="submit" class="deInputs" value="{SMS_Accept}"/>
        </form>
    </th>
</tr>
<tr>
    <th class="c pad deTab" id="deTab02">{SMS_TAB_2}</th>
</tr>
<tr>
    <th class="c pad deTab" id="deTab03">{SMS_TAB_3}</th>
</tr>
<tr>
    <th class="c pad deTab" id="deTab04">{SMS_TAB_4}</th>
</tr>
<tr>
    <th class="c pad" colspan="6">{DarkEnergySMSInfo}</th>
</tr>
