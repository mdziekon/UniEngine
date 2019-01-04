<script>
var JSLang = {'TP_timeOnlyTitle': 'Wybierz Czas', 'timeText': 'Czas', 'hourText': 'Godzina','minuteText': 'Minuta','secondText': 'Sekunda','millisecText': 'Milisekunda','currentText': 'Teraz','closeText': 'Gotowe'};
</script>
<script src="../dist/js/admin/banslist_body.cachebuster-1545956361123.min.js" type="text/javascript"></script>
<script src="../libs/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="../libs/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js" type="text/javascript"></script>
<script src="../libs/jquery-ui-datepicker-pl/jquery-ui-datepicker-pl.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="../dist/css/admin/banslist_body.cachebuster-1546564327123.min.css" />
<link rel="stylesheet" type="text/css" href="../libs/jquery-ui/jquery-ui.min.css" />
{Insert_Scripts}
<br/>
<form action="banslist.php" method="post" id="thisForm">
    <input type="hidden" name="send" value="yes"/>
    <input type="hidden" name="page" value=""/>
    <table style="width: 1000px;">
        <tr>
            <td class="c" colspan="{Colspan}">{Page_Title}</td>
        </tr>
        <tr>
            <th class="pad5" colspan="{Colspan}">
                <span class="fl">
                    <span class="marg10">Gracze: <input type="text" name="users" class="pad3" value="{Insert_Input_Users}" style="width: 250px;"/></span>
                    <span class="marg10">Przedział Czasu: <input type="text" name="date_from" class="pad3" value="{Insert_Input_DateFrom}"/> - <input type="text" name="date_to" class="pad3" value="{Insert_Input_DateTo}"/></span>
                </span>
                <span class="fr marg10">
                    <input type="submit" value="Szukaj" class="pad3" style="font-weight: 700; width: 100px;"/>
                    <input type="button" id="reset" value="Resetuj" class="pad3" style="font-weight: 700; width: 100px;"/>
                </span>
            </th>
        </tr>
        <tr>
            <td class="c center pad2" style="width: 30px;">{Header_ID}</td>
            <td class="c center pad2" style="width: 100px;">{Header_User}</td>
            <td class="c center pad2" style="width: 120px;">{Header_StartTime}</td>
            <td class="c center pad2" style="width: 75px;">{Header_Duration}</td>
            <td class="c center pad2" style="width: 120px;">{Header_EndTime}</td>
            <td class="c center pad2" style="width: 120px;">{Header_Status}</td>
            <td class="c center pad2" style="width: 175px;">{Header_Reason}</td>
            <td class="c center pad2" style="width: 100px;">{Header_Giver}</td>
            <td class="c center pad2" style="width: 150px;">{Header_Indicators}</td>
        </tr>
        {Insert_Pagination}
        {Insert_Rows}
        {Insert_Pagination}
    </table>
</form>
