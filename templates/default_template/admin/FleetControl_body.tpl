<style>
.pad {
    padding: 5px;
}
.mark {
    width: 100px;
}
.fArr, .fCar, .mark {
    cursor: pointer;
}
.fID {
    font-size: 50%;
}
.red {
    color: red;
}
.orange {
    color: orange;
}
.lime {
    color: lime;
}
.blue {
    color: #87CEEB;
}
.cent {
    text-align: center;
}
.help_th {
    background: none;
    border: none;
}
.cargo_res {
    min-width: 70px;
}
.button {
    font-weight: 700;
    padding: 3px;
}
.inv {
    visibility: hidden;
}
.hide {
    display: none;
}
.padOpt {
    padding: 2px 10px;
}
.checkBox {
    margin: 0px 2px;
    vertical-align: text-bottom;
}
.rightOpt {
    margin: 4px 0px;
}
</style>
<script>
$(document).ready(function()
{
    $(".help").tipTip({delay: 0, maxWidth: "200px", attribute: 'title'});
    $(".fArr").tipTip({delay: 0, maxWidth: "300px", attribute: 'title'});
    $(".fCar").tipTip({delay: 0, maxWidth: "500px", attribute: 'title'});

    var SelectAllState = false;
    var SelectableBoxes = $('.canSel,.selAll');
    $('.selAll').click(function()
    {
        if(SelectAllState === false)
        {
            var SetCheck = true;
        }
        else
        {
            var SetCheck = false;
        }
        SelectAllState = SetCheck;
        SelectableBoxes.attr('checked', SetCheck);
    });

    $('.confirm').click(function()
    {
        return confirm('{Ask_AreYouSure}');
    });

    $('.actionRetreat').click(function()
    {
        $('input[name="cmd"]').val('1');
    });
    $('.actionFallback').click(function()
    {
        $('input[name="cmd"]').val('2');
    });

    $('.checkBox').click(function()
    {
        $('[class="'+$(this).attr('class')+'"]').attr('checked', $(this).is(':checked'));
    });
});
</script>
{ChronoApplets}
<br />
<form action="FleetControl.php" method="post" style="margin: 0px;">
    <input type="hidden" name="process" value="1"/>
    <input type="hidden" name="cmd" value=""/>
    <table width="1000">
        <tbody class="{Hide_Message}">
            <tr>
                <th colspan="10" class="pad2">&nbsp;{MessageText}</th>
            </tr>
            <tr class="inv">
                <td class="fID">&nbsp;</td>
            </tr>
        </tbody>
        <tbody>
            <tr>
                <td colspan="10" class="c center">{PageTitle}<span style="float: right;"><a href="">({FleetControl_Refresh})</a></span></td>
            </tr>
            <tr>
                <td class="pad2 c" style="width: 25px;">&nbsp;</td>
                <td class="pad2 c center" style="width: 45px;">{Fleet_ID}</td>
                <td class="pad2 c center">{Fleet_Owner}</td>
                <td class="pad2 c center">{Fleet_Mission}</td>
                <td class="pad2 c center">{Fleet_Array}</td>
                <td class="pad2 c center" style="width: 55px;">{Fleet_Cargo}</td>
                <td class="pad2 c center" style="width: 150px;">{Fleet_Start}</td>
                <td class="pad2 c center" style="width: 125px;">{Fleet_End}</td>
                <td class="pad2 c center">{Fleet_End_owner}</td>
                <td class="pad2 c center" style="width: 125px;">{Fleet_Back_time}</td>
            </tr>
            <tr class="{Hide_AllOptions}">
                <th class="pad2"><input type="checkbox" class="selAll"/></th>
                <th class="padOpt" colspan="9">
                    <span class="fl">
                        <input type="submit" class="actionRetreat button confirm orange {Hide_OptionRetreat}" value="{FleetControl_Retreat}"/>
                        <input type="submit" class="actionFallback button confirm red {Hide_OptionFallback}" value="{FleetControl_Fallback}"/>
                    </span>
                    <span class="fr rightOpt">
                        {FleetControl_SendMsg} <input type="checkbox" name="sendNotice" class="checkBox cSendMsg" checked="checked"/>
                    </span>
                </th>
            </tr>
            {Rows}
            <tr class="{Hide_AllOptions}">
                <th class="pad2"><input type="checkbox" class="selAll"/></th>
                <th class="padOpt" colspan="9">
                    <span class="fl">
                        <input type="submit" class="actionRetreat button confirm orange {Hide_OptionRetreat}" value="{FleetControl_Retreat}"/>
                        <input type="submit" class="actionFallback button confirm red {Hide_OptionFallback}" value="{FleetControl_Fallback}"/>
                    </span>
                    <span class="fr rightOpt">
                        {FleetControl_SendMsg} <input type="checkbox" name="sendNotice" class="checkBox cSendMsg" checked="checked"/>
                    </span>
                </th>
            </tr>
        </tbody>
    </table>
</form>
