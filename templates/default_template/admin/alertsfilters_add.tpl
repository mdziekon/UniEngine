<style>
.inv {
    visibility: hidden;
}
.red {
    color: red !important;
}
.lime {
    color: lime !important;
}
</style>
<script>
$(document).ready(
function()
{
    jQuery.fn.extend({
        insertAtCaret: function(myValue){
          return this.each(function(i) {
            if (document.selection) {
              this.focus();
              sel = document.selection.createRange();
              sel.text = myValue;
              this.focus();
            }
            else if (this.selectionStart || this.selectionStart == '0') {
              var startPos = this.selectionStart;
              var endPos = this.selectionEnd;
              var scrollTop = this.scrollTop;
              this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
              this.focus();
              this.selectionStart = startPos + myValue.length;
              this.selectionEnd = startPos + myValue.length;
              this.scrollTop = scrollTop;
            } else {
              this.value += myValue;
              this.focus();
            }
          })
        }
    });

    var SelectedCond = '';
    var TrueSubmit = false;

    $('.hide').hide();

    $('#conditions').change(function()
    {
        $('#conditions option:selected').each(function()
        {
            if($(this).val() !== ''){
                $('.hide').hide();
                $('.nl').show();
                $('.'+$(this).attr('id')).show();
                $('.giveVal:visible').val('');
                SelectedCond = $(this).attr('id');
                if($(this).attr('id') == 'cond5'){
                    $('.giveVal:visible').attr('size', '50');
                } else {
                    $('input.giveVal:visible').attr('size', '20');
                }
            } else {
                $('.hide').hide();
                SelectedCond = '';
            }
        });
    });
    $('#conditions').keyup(function()
    {
        $(this).change();
    });

    $('#addFilter').click(function()
    {
        TrueSubmit = true;
        if(confirm('{AreYouSure_Add}')){
            $('[name="doWhat"]').val('save');
            $('#thisForm').submit();
        }
    });
    $('#checkFilter').click(function()
    {
        TrueSubmit = true;
        $('[name="doWhat"]').val('check');
        $('#thisForm').submit();
    });

    $('.clear').click(function()
    {
        if(confirm('{AreYouSure_Clear}')){
            $("#code").val('');
        }
    });

    $('#thisForm').submit(function()
    {
        if(TrueSubmit === false){
            if(SelectedCond !== ''){
                if($('#'+SelectedCond).val().indexOf('xyz') !== -1){
                    if($('.giveVal:visible').val() !== ''){
                        $('#code').insertAtCaret($('#'+SelectedCond).val().replace('xyz', $('.giveVal:visible').val()));
                    } else {
                        alert('{Alert_GiveValFirst}');
                    }
                } else {
                    $('#code').insertAtCaret($('#'+SelectedCond).val());
                }
            }

            return false;
        }
    });

});
</script>
<br />
<form action="{ThisFormAction}" method="post" id="thisForm">
    <input type="hidden" name="doWhat" value="save"/>
    {InsertOnEdit}
    <table width="950">
            {System_MSG}
        <tr>
            <td class="c" colspan="2"><span style="float: left;">{Filters_Add}</span><span style="float: right;">(<a href="?cmd=list">{CMD_GoBack}</a>)</span></td>
        </tr>
        <tr>
            <th class="c pad5" width="100px">{QuickConditions}</th>
            <th class="c pad5">
                <select id="conditions" style="text-align: center;">
                    <option value="">---</option>
                    <option value="userPresent(xyz)" id="cond1">{Condition_1}</option>
                    <option value="ipPresent(xyz)" id="cond2">{Condition_2}</option>
                    <option value="userIsSender(xyz)" id="cond3">{Condition_3}</option>
                    <option value="userIsTarget(xyz)" id="cond4">{Condition_4}</option>
                    <option value="logIPCount(xyz)" id="cond5">{Condition_5}</option>
                    <option value="inPlace(xyz)" id="cond6">{Condition_6}</option>
                    <option value="AlertSenderIs(xyz)" id="cond7">{Condition_7}</option>
                </select>
                <span class="nl hide">
                    <br /><br />
                </span>
                <span class="cond1 cond3 cond4 hide">
                    {Give_UserID}
                </span>
                <span class="cond2 hide">
                    {Give_IPID}
                </span>
                <span class="cond5 hide" style="margin-bottom: 4px;">
                    {Give_LogIPCount}<br />
                </span>
                <span class="cond1 cond2 cond3 cond4 cond5 hide">
                     <input type="text" size="20" class="giveVal"/> <input type="submit" value="{AddCondition}"/>
                </span>
                <span class="cond6 hide">
                    <select class="giveVal">
                        <option value="1">{CondSelector_6_1}</option>
                        <option value="2">{CondSelector_6_2}</option>
                        <option value="3">{CondSelector_6_3}</option>
                        <option value="4">{CondSelector_6_4}</option>
                    </select>
                    <input type="submit" value="{AddCondition}"/>
                </span>
                <span class="cond7 hide">
                    <select class="giveVal">
                        <option value="1">{CondSelector_7_1}</option>
                        <option value="2">{CondSelector_7_2}</option>
                        <option value="3">{CondSelector_7_3}</option>
                        <option value="4">{CondSelector_7_4}</option>
                        <option value="5">{CondSelector_7_5}</option>
                    </select>
                    <input type="submit" value="{AddCondition}"/>
                </span>
            </th>
        </tr>
        <tr>
            <th class="c pad5">{FilterCode}<br />(<a href="#" class="clear">{CMD_Clear}</a>)</th>
            <th class="c pad5">
                <textarea name="code" id="code" style="height: 70px;">{CodePost}</textarea>
            </th>
        </tr>
        <tr>
            <th class="c pad5">{ActionType}</th>
            <th class="c pad5">
                <select name="action" style="text-align: center;">
                    <option value="">---</option>
                    <option value="1" {ActionType_Select1}>{ActionType_1}</option>
                    <option value="2" {ActionType_Select2}>{ActionType_2}</option>
                </select>
            </th>
        </tr>
        <tr>
            <th class="c pad5">{TurnOff}</th>
            <th class="c pad5">
                <input type="checkbox" name="turnoff" {TurnOffChecked}/>
            </th>
        </tr>
        <tr>
            <th class="c pad5" colspan="2">
                <input type="button" value="{CheckFilter}" id="checkFilter"/> <input type="button" value="{AddFilter}" id="addFilter"/>
            </th>
        </tr>
    </table>
</form>
