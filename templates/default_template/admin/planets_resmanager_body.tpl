<style>
.pad3 {
    padding: 3px !important;
}

/* Elements Declarations */
.typeTab {
    width: 25%;
    cursor: pointer;
}
.typeTab:hover {
    background-color: #455B87;
    border-color: #4E6797;
}
.typeTab.active {
    background-color: #5773A8 !important;
    border-color: #6881B1 !important;
}

.cmdRadio {
    margin: 0px 35px;
}
.cmdRadio > input {
    margin: 0px 5px 0px 0px;
    vertical-align: text-top;
}
.resTH {
    width: 50%;
    padding: 2px;
}
.resTH:first-child {
    text-align: right;
    padding-right: 8px;
}
.resTH:last-child {
    text-align: left;
    padding-left: 8px;
}
.resInput {
    width: 80%;
    padding: 3px;
}

#thisForm > table {
    margin: -2px;
}
#submit {
    width: 95%;
    padding: 3px;
    color: lime;
    font-weight: 700;
}
</style>
<script>
$(document).ready(function()
{
    // Internal Functions
    function addDots(value)
    {
        value += '';
        var rgx = /(\d+)(\d\d\d)/;
        while(rgx.test(value))
        {
            value = value.replace(rgx, '$1' + '.' + '$2');
        }
        return value;
    }

    function removeNonDigit(Value)
    {
        Value += '';
        Value = Value.replace(/[^0-9]/g, '');
        return Value;
    }

    $.fn.prettyInputBox = function()
    {
        return this.each(function()
        {
            if(AllowPrettyInputBox !== undefined && AllowPrettyInputBox === true)
            {
                Value = removeNonDigit($(this).val());
                Value = addDots(Value);
                $(this).val(Value);
            }
        });
    }

    var $Tabs = $('.typeTab');
    var $TabContent = $('.tabContent');
    var $Inputs_TabNo = $('input[name="tab"]');
    var AllowPrettyInputBox = true;

    // Tabs Handler
    $Tabs.click(function()
    {
        ThisID = $(this).attr('data-tabID');

        $Tabs.removeClass('active');
        $(this).addClass('active');

        $TabContent.hide();
        $('#tab'+ThisID).show();
        $Inputs_TabNo.val(ThisID);
    });

    // Input Handler
    $('.resInput').change(function()
    {
        $(this).prettyInputBox();
    })
    .keyup(function()
    {
        $(this).change();
    })
    .keydown(function()
    {
        $(this).change();
    });

    // Default
    var DefaultTab = parseInt('0{Insert_DefaultTab}', 10);
    $TabContent.hide();
    $Tabs.get(DefaultTab).click();
});
</script>
<br/>
<form action="" method="post" id="thisForm">
    <input type="hidden" name="sent" value="1"/>
    <input type="hidden" name="tab" value="1"/>
    <table style="width: 800px;">
        <tr>
            <th class="pad5 {MsgBox_Color}" colspan="4">{MsgBox_Text}</th>
        </tr>
        <tr>
            <th class="pad5" colspan="4">
                {Table_PlanetID}: <input type="text" name="planetID" class="pad3" value="{Insert_PreviousPlanetID}"/>
            </th>
        </tr>
        <tr>
            <th class="c pad3 typeTab" data-tabID="1">{Table_Tabs_1}</th>
            <th class="c pad3 typeTab" data-tabID="2">{Table_Tabs_2}</th>
            <th class="c pad3 typeTab" data-tabID="3">{Table_Tabs_3}</th>
            <th class="c pad3 typeTab" data-tabID="4">{Table_Tabs_4}</th>
        </tr>
    </table>
    <table style="width: 800px;">
        <tr>
            <th class="pad5" colspan="2">
                <span class="cmdRadio"><input type="radio" name="cmd" value="add" id="cmd_add" {Insert_SelectCMD_add}/> <label for="cmd_add">{Table_CMD_Add}</label></span>
                <span class="cmdRadio"><input type="radio" name="cmd" value="set" id="cmd_set" {Insert_SelectCMD_set}/> <label for="cmd_set">{Table_CMD_Set}</label></span>
                <span class="cmdRadio"><input type="radio" name="cmd" value="sub" id="cmd_sub" {Insert_SelectCMD_sub}/> <label for="cmd_sub">{Table_CMD_Substract}</label></span>
            </th>
        </tr>
        <tbody class="tabContent" id="tab1">
            {Insert_Rows_Res}
        </tbody>
        <tbody class="tabContent" id="tab2">
            {Insert_Rows_Buildings}
        </tbody>
        <tbody class="tabContent" id="tab3">
            {Insert_Rows_Fleet}
        </tbody>
        <tbody class="tabContent" id="tab4">
            {Insert_Rows_Defense}
        </tbody>
        <tr>
            <th class="pad5" colspan="2">
                <input type="submit" id="submit" value="{Table_Submit}"/>
            </th>
        </tr>
    </table>
</form>
