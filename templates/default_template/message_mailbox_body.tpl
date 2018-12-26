<tr{CurrMSG_IsUnread}>
    <input name="sm{CurrMSG_ID}" type="hidden" value="1" />
    <th rowspan="3" class="eMark {CurrMSG_color}">
        <input name="del{CurrMSG_ID}" type="checkbox" {CurrMSG_HideCheckbox}/>
    </th>
    <th class="eFrom">{CurrMSG_from}</th>
    <th>
        {CurrMSG_subject}
    </th>
</tr>
<tr{CurrMSG_IsUnread}>
    <td class="b eDate" colspan="2"><span class="fLeft">{CurrMSG_Unread}{CurrMSG_send}</span><span class="fRigh">{CurrMSG_buttons}</span></td>
</tr>
<tr{CurrMSG_IsUnread}>
    <td class="b msgrow tleft" colspan="2">{CurrMSG_text}</td>
</tr>
{AddMSG_parsed}
