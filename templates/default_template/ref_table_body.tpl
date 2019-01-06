<style>
.lime {
    color: lime;
}
.red {
    color: red;
}
.pad {
    padding: 5px;
}
</style>
<br/>
<table width="700">
    <tbody>
        <tr>
            <td class="c" colspan="3">{Table_Title}</td>
        </tr>
        <tr>
            <th colspan="3" class="b pad">
                {reftable_desc}
                <br /><br />
                {your_referral_link_is}
                <br/>
                <input type="text" id="rlink" size="70" style="border: 1px solid #A1A1A1;" onclick="document.getElementById('rlink').select();" value="{referralLink}"/>
                <br/><br/>
                {referring_info}
            </th>
        </tr>
        <tr><td style="visibility: hidden; font-size: 5px;">&nbsp;</td></tr>
        <tr>
            <td colspan="3" class="c">{reftable}</td>
        </tr>
        <tr>
            <th class="c" style="width: 32%;">{Who}</th>
            <th class="c" style="width: 36%;">{Date}</th>
            <th class="c" style="width: 32%;">{Provisions_Granted}</th>
        </tr>
        {Rows}
    </tbody>
</table>
