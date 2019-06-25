<style>
label {
    display: inline-block;
    width: 30%;
    padding: 0 10px;
    text-align: right;
}
.w90p {
    width: 90%;
}
.w60p {
    width: 60%;
}
.w30p {
    width: 30%;
}
.tLeft {
    text-align: left;
}
.margB {
    margin-bottom: 5px;
}
.button {
    font-weight: 700;
}
.ui-datepicker-calendar > thead > tr > th {
    background-color: transparent;
    color: black;
}
</style>
<script>
$(document).ready(function()
{
    $('#srch_date').datepicker({maxDate: '0', beforeShow: function(){ setTimeout(function(){ $('#ui-datepicker-div').css({'z-index': 1000}); },1);}});
});
</script>
<script src="../libs/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="../libs/jquery-ui-datepicker-{JS_DatePicker_TranslationLang}/jquery-ui-datepicker-{JS_DatePicker_TranslationLang}.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="../libs/jquery-ui/jquery-ui.min.css" />
<br/>
<form action="?" method="post">
    <input type="hidden" name="send" value="1"/>
    <table style="width: 900px;">
        <tr>
            <td class="c" colspan="5">{Page_Title}</td>
        </tr>
        <tr>
            <th class="pad2" colspan="4">
                <div class="fl w30p tLeft pad5">
                    <label for="srch_sender">{Table_Sender}:</label><input type="text" class="pad2 w60p margB" id="srch_sender" name="srch_sender" value="{Insert_srch_sender}"/><br/>
                    <label for="srch_owner">{Table_Owner}:</label><input type="text" class="pad2 w60p margB" id="srch_owner" name="srch_owner" value="{Insert_srch_owner}"/>
                </div>
                <div class="fl w30p tLeft pad5">
                    <label for="srch_date">{Table_Date}:</label><input type="text" class="pad2 w60p margB" id="srch_date" name="srch_date" value="{Insert_srch_date}"/>
                </div>
            </th>
            <th class="pad2">
                <input type="submit" class="button pad5 w90p" value="{Table_Submit}"/>
            </th>
        </tr>
        <tbody style="{Insert_HideResults}">
            <tr>
                <td class="c center" colspan="5">{Table_Result}</td>
            </tr>
            <tr>
                <th class="pad5" colspan="5">{Insert_BashOverallResult}<br/><span class="tLeft orange">{Insert_BashList}</span></th>
            </tr>
            <tr style="{Insert_HideFleetRows}">
                <td class="c center" style="width: 100px;">{Table_Head_ID}</td>
                <td class="c center" style="width: 200px;">{Table_Head_Date}</td>
                <td class="c center" style="width: 100px;">{Table_Head_Mission}</td>
                <td class="c center" style="width: 300px;">{Table_Head_Target}</td>
                <td class="c center" style="width: 100px;">{Table_Head_ReportID}</td>
            </tr>
            {Insert_FleetRows}
        </tbody>
    </table>
</form>
