<tbody>
    <tr>
        <td colspan="3" class="c">
            {MailChange_Title}
        </td>
    </tr>
    <tr>
        <th colspan="3" class="c">
            {MailChange_Text}
            <br/><br/>
            {ChangeProcess_Status}
            <form
                action="email_change.php?hash=none"
                method="post"
                style="{ChangeProcess_HideFormStyle}"
            >
                <input
                    type="submit"
                    style="font-weight: bold;"
                    value="{MailChange_Buto}"
                />
            </form>
        </th>
    </tr>
    <tr>
        <th class="inv">&nbsp;</th>
    </tr>
</tbody>
