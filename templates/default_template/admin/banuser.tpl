<style>.hide{visibility: hidden;}</style>
<br />
<form action="banuser.php" method="post">
    <input type="hidden" name="save" value="yes"/>
    <table style="width: 600px;">
        <tbody{HideInfoBox}>
            <tr>
                <th class="pad5 {InsertInfoBoxColor}" colspan="2">{InsertInfoBoxText}</th>
            </tr>
            <tr class="hide"><td style="min-height: 5px;"></td></tr>
        </tbody>
        <tr>
            <td class="c" colspan="2">{Form_General}</td>
        </tr>
        <tr>
            <th style="width: 200px;" class="pad5">{Form_UserInput}</th>
            <th style="width: 400px;" class="pad5">
                <textarea name="users" style="width: 375px; padding: 3px;">{Insert_SearchBox}{InsertUsernames}</textarea>
            </th>
        </tr>
        <tr>
            <th class="pad5">{Form_Reason}</th>
            <th class="pad5">
                <textarea name="reason" style="width: 375px; padding: 3px;"></textarea>
            </th>
        </tr>
        <tr>
            <td class="c" colspan="2">{Form_Period}</td>
        </tr>
        <tr>
            <th class="pad2">{Form_Days}</th>
            <th class="pad2">
                <input type="text" name="period_days" maxlength="4" style="width: 35px; padding: 3px;"/>
            </th>
        </tr>
        <tr>
            <th class="pad2">{Form_Hours}</th>
            <th class="pad2">
                <input type="text" name="period_hours" maxlength="2" style="width: 35px; padding: 3px;"/>
            </th>
        </tr>
        <tr>
            <th class="pad2">{Form_Minutes}</th>
            <th class="pad2">
                <input type="text" name="period_mins" maxlength="2" style="width: 35px; padding: 3px;"/>
            </th>
        </tr>
        <tr>
            <th class="pad2">{Form_Seconds}</th>
            <th class="pad2">
                <input type="text" name="period_secs" maxlength="2" style="width: 35px; padding: 3px;"/>
            </th>
        </tr>
        <tr>
            <th class="pad2">{Form_ExtendPrevious}</th>
            <th class="pad2">
                <input type="checkbox" name="extend" checked=""/>
            </th>
        </tr>
        <tr>
            <td class="c" colspan="2">{Form_Other}</td>
        </tr>
        <tr>
            <th class="pad2">{Form_SetVacationOn}</th>
            <th class="pad2">
                <input type="checkbox" name="vacation" checked=""/>
            </th>
        </tr>
        <tr>
            <th class="pad2">{Form_SetRetreatOwnOn}</th>
            <th class="pad2">
                <input type="checkbox" name="fleet_retreat_own" checked=""/>
            </th>
        </tr>
        <tr>
            <th class="pad2">{Form_SetRetreatOthersOn}</th>
            <th class="pad2">
                <input type="checkbox" name="fleet_retreat_others" checked=""/>
            </th>
        </tr>
        <tr>
            <th class="pad2 red">{Form_SetBlockade_CookieStyle}</th>
            <th class="pad2">
                <input type="checkbox" name="cookies"/>
            </th>
        </tr>
        <tr class="hide"><td style="min-height: 5px;"></td></tr>
        <tr>
            <th class="pad5" colspan="2">
                <input type="submit" value="{Form_GiveBan}" class="pad5" style="font-weight: 700;"/>
            </th>
        </tr>
    </table>
</form>
