<style>
.hide{display:none;}
.inv{visibility: hidden;}
.pad3{padding:3px;}
.w600px{width:600px;margin:-2px;}
.th1{width: 200px;}
.th2{width: 400px;}
.Tab{width:50%;cursor:pointer;}
.Tab:hover{background-color:#455C87;border-color:#526EA3;}
.TabSelect{background-color:#5773A8 !important;border-color:#7189B7 !important;}
.ui-datepicker-calendar > thead > tr > th{background: none; color: black;}
</style>
<script src="../libs/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="../libs/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js" type="text/javascript"></script>
<script src="../libs/jquery-ui-datepicker-{JS_DatePicker_TranslationLang}/jquery-ui-datepicker-{JS_DatePicker_TranslationLang}.min.js" type="text/javascript"></script>
<script>
var SelectedTab = '{Insert_SelectedTab}';
</script>
<script>
$(document).ready(function()
{
    var DateTimePickerSettings = {
        showButtonPanel: false,
        showSecond: true,
        beforeShow: function() {
            setTimeout(function() {
                $('#ui-datepicker-div').css({'z-index': 1000});
            }, 1);
        }
    };

    $('[name="recude_date"]').datetimepicker(DateTimePickerSettings);

    $('.Tab').click(function()
    {
        $('.Cont').hide(0);
        $('.Tab').removeClass('TabSelect');
        $('#'+$(this).attr('id').replace('Tab', 'Cont')).show(0);
        $(this).addClass('TabSelect');
        $('[name="reduce_type"]').val($(this).attr('id').replace('Tab', ''));
    });

    if(SelectedTab == '' || $('#Tab'+SelectedTab).length == 0)
    {
        $('#Tab01').click();
    }
    else
    {
        $('#Tab'+SelectedTab).click();
    }
});
</script>
<link rel="stylesheet" type="text/css" href="../libs/jquery-ui/jquery-ui.min.css" />
<br />
<form action="reduceban.php" method="post">
    <input type="hidden" name="send" value="yes"/>
    <input type="hidden" name="reduce_type" value="01"/>
    <table class="w600px">
        <tbody{HideInfoBox}>
            <tr>
                <th class="pad5 {InsertInfoBoxColor}" colspan="2">{InsertInfoBoxText}</th>
            </tr>
            <tr class="inv"><td style="min-height: 5px;"></td></tr>
        </tbody>
        <tr>
            <td class="c" colspan="2">{Page_Title}</td>
        </tr>
        <tr>
            <th class="pad5 th1">{Form_UserInput}</th>
            <th class="pad5 th2">
                <textarea name="users" style="width: 375px; padding: 3px;">{Insert_SearchBox}{InsertUsernames}</textarea>
            </th>
        </tr>
        <tr>
            <td class="c" colspan="2">{Form_Option}</td>
        </tr>
    </table>
    <table class="w600px">
        <th class="pad3 Tab" id="Tab01">{Form_OptDate}</th>
        <th class="pad3 Tab" id="Tab02">{Form_OptTimeSpan}</th>
    </table>
    <table class="w600px">
        <tbody class="Cont" id="Cont01">
            <tr>
                <th class="pad2 th1">{Form_PickDate}</th>
                <th class="pad2 th2">
                    <input type="text" name="recude_date" class="pad3" style="width: 150px;"/>
                </th>
            </tr>
        </tbody>
        <tbody class="Cont" id="Cont02">
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
        </tbody>
        <tr class="inv"><td style="min-height: 5px;"></td></tr>
        <tr>
            <th class="pad5" colspan="2">
                <input type="submit" value="{Form_Unban}" class="pad5" style="font-weight: 700;"/>
            </th>
        </tr>
    </table>
</form>
