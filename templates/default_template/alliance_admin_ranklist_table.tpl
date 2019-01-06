<style>
.hide {
    display: none;
}
.inv {
    visibility: hidden;
}
.markHover {
    background-color: #455B87;
    border-color: #526EA3;
}
.markSelect {
    background-color: #4E6797;
    border-color: #607BAF;
}
.bold, .delButton {
    font-weight: bold;
}
.textFoc {
    background-color: #455B87;
    border-color: #607BAF !important;
}
input[type='text'] {
    border-color: #496392;
}
.ico_1 {
    background: url('images/user.png') no-repeat 0pt 0pt;
}
.ico_2 {
    background: url('images/users.png') no-repeat 0px 0px;
}
.ico_3 {
    background: url('images/users.png') no-repeat 0pt 0pt;
}
.ico_4 {
    background: url('images/msg.png') no-repeat 0pt 0pt;
}
.ico_5 {
    background: url('images/edit.png') no-repeat 0pt 0pt;
}
.ico_6 {
    background: url('images/request.png') no-repeat 0pt 0pt;
}
.ico_7 {
    background: url('images/request.png') no-repeat 0pt 0pt;
}
.ico_8 {
    background: url('images/users.png') no-repeat 0pt 0pt;
}
.ico_9 {
    background: url('images/edit.png') no-repeat 0pt 0pt;
}
.ico_10 {
    background: url('images/delete.png') no-repeat 2px 2px;
}
.ico_11 {
    background: url('images/shield.png') no-repeat 0px 0px;
}
.ico_12 {
    background: url('images/newmail.png') no-repeat 0px 0px;
}
.ico_13 {
    background: url('images/chat.png') no-repeat 2px 2px;
}
.ico_1, .ico_2, .ico_3, .ico_4, .ico_5, .ico_6, .ico_7, .ico_8, .ico_9, .ico_10, .ico_11, .ico_12, .ico_13 {
    padding-left: 16px;
    cursor: help;
}
.dash {
    border-bottom: 1px white dashed;
}
.na_nonam, .na_nochg, .na_adchg, .na_nodel, .adm_nochg, .adm_nodel, .nc_nodel, .memCount {
    cursor: help;
}
</style>
<script>
$(document).ready(function()
{
    $(".mark").hover(function()
    {
        $(this).children().addClass('markHover');
    }, function()
    {
        $(this).children().removeClass('markHover');
    })
    .click(function()
    {
        $('.markSelect').removeClass('markSelect');
        $(this).children().addClass('markSelect');
    })
    .children().addClass('pad5');
    $('input[type=text]').focus(function()
    {
        $(this).addClass('textFoc');
    }).blur(function()
    {
        if($(this).hasClass('textFoc')){
            $(this).removeClass('textFoc');
        }
    });

    $('.delButton').click(function()
    {
        if(confirm('{ADM_RkL_RUSure}')){
            $('.change').val($(this).attr('id'));
            $('.sendForm').submit();
        }
    });
    $('.chgButton').click(function()
    {
        if(confirm('{ADM_RkL_RUSure2}')){
            $('.change').val('saveChg');
            $('.sendForm').submit();
        }
    });

    $('[name^=chgData]:checkbox, [name^=opt]:checkbox').click(function()
    {
        if($(this).attr('name').indexOf('chgData') !== -1){
            var Temp    = $(this).attr('name').substr(8);
            var Temp2   = Temp.indexOf(']');
            var RankID  = Temp.substr(0, Temp2);
            var OptID   = Temp.substr(Temp2 + 2, Temp.length - (Temp2 + 3));
            var SetName = 'chgData['+RankID+']';
        } else {
            var OptID    = $(this).attr('name').substr(4).replace(']', '');
            var SetName = 'opt';
        }

        var isChecked = $('[name="'+$(this).attr('name')+'"]:checked').length;

        if(isChecked == 1){
            if(OptID == 1){
                $('[name^="'+SetName+'"]:checkbox').attr('checked', true);
            } else if(OptID == 3){
                $('[name^="'+SetName+'[2]"]:checkbox').attr('checked', true);
            } else if(OptID == 7){
                $('[name^="'+SetName+'[6]"]:checkbox').attr('checked', true);
            }
        } else {
            if(OptID == 6){
                $('[name^="'+SetName+'[7]"]:checkbox').attr('checked', false);
            } else if(OptID == 2){
                $('[name^="'+SetName+'[3]"]:checkbox').attr('checked', false);
            }
            $('[name^="'+SetName+'[1]"]:checkbox').attr('checked', false);
        }
    });

    $('.ico_1').tipTip({maxWidth: 250, minWidth: 250, edgeOffset: 10, delay: 0, content: '{ADM_RkL_Info1}'});
    $('.ico_2').tipTip({maxWidth: 250, minWidth: 250, edgeOffset: 10, delay: 0, content: '{ADM_RkL_Info2}'});
    $('.ico_3').tipTip({maxWidth: 250, minWidth: 250, edgeOffset: 10, delay: 0, content: '{ADM_RkL_Info3}'});
    $('.ico_4').tipTip({maxWidth: 250, minWidth: 250, edgeOffset: 10, delay: 0, content: '{ADM_RkL_Info4}'});
    $('.ico_5').tipTip({maxWidth: 250, minWidth: 250, edgeOffset: 10, delay: 0, content: '{ADM_RkL_Info5}'});
    $('.ico_6').tipTip({maxWidth: 250, minWidth: 250, edgeOffset: 10, delay: 0, content: '{ADM_RkL_Info6}'});
    $('.ico_7').tipTip({maxWidth: 250, minWidth: 250, edgeOffset: 10, delay: 0, content: '{ADM_RkL_Info7}'});
    $('.ico_8').tipTip({maxWidth: 250, minWidth: 250, edgeOffset: 10, delay: 0, content: '{ADM_RkL_Info8}'});
    $('.ico_9').tipTip({maxWidth: 250, minWidth: 250, edgeOffset: 10, delay: 0, content: '{ADM_RkL_Info9}'});
    $('.ico_10').tipTip({maxWidth: 250, minWidth: 250, edgeOffset: 10, delay: 0, content: '{ADM_RkL_Info10}'});
    $('.ico_11').tipTip({maxWidth: 250, minWidth: 250, edgeOffset: 10, delay: 0, content: '{ADM_RkL_Info11}'});
    $('.ico_12').tipTip({maxWidth: 250, minWidth: 250, edgeOffset: 10, delay: 0, content: '{ADM_RkL_Info12}'});
    $('.ico_13').tipTip({maxWidth: 250, minWidth: 250, edgeOffset: 10, delay: 0, content: '{ADM_RkL_Info13}'});

    $('.na_nodel').tipTip({maxWidth: 200, edgeOffset: 10, delay: 0, content: '{ADM_RkL_NoAccess2Del}'});
    $('.na_nonam').tipTip({maxWidth: 200, edgeOffset: 10, delay: 0, content: '{ADM_RkL_NoAccess2Nam}'});
    $('.na_nochg').tipTip({maxWidth: 200, edgeOffset: 10, delay: 0, content: '{ADM_RkL_NoAccess2Chg}'});
    $('.na_adchg').tipTip({maxWidth: 200, edgeOffset: 10, delay: 0, content: '{ADM_RkL_NoAccess2ACh}'});
    $('.adm_nochg').tipTip({maxWidth: 200, edgeOffset: 10, delay: 0, content: '{ADM_RkL_ChangeAdminRk}'});
    $('.adm_nodel').tipTip({maxWidth: 200, edgeOffset: 10, delay: 0, content: '{ADM_RkL_DeleteAdminRk}'});
    $('.nc_nodel').tipTip({maxWidth: 200, edgeOffset: 10, delay: 0, content: '{ADM_RkL_DeleteDefNewRk}'});

    $('.memCount').tipTip({maxWidth: 250, edgeOffset: 10, delay: 0, content: '{ADM_RkL_MemCount}'});
});
</script>
<br/>
<table width="650">
    <tr class="{HideInfoBox}">
        <td class="c pad5 {InfoBoxColor}" colspan="16">{InfoBoxText}</td>
    </tr>
    <tr class="inv {HideInfoBox}">
        <td></td>
    </tr>
    <tr>
        <td class="c" colspan="16">{ADM_RankList_Title}<b style="float: right">({ADM_RanksCount}: {RankCount})</b></td>
    </tr>
    <tr>
        <th width="100px">{ADM_RkL_Name}</th>
        <th width="25px"><b class="ico_1"></b></th>
        <th width="25px"><b class="ico_2"></b></th>
        <th width="25px"><b class="ico_3"></b></th>
        <th width="25px"><b class="ico_4"></b></th>
        <th width="25px"><b class="ico_5"></b></th>
        <th width="25px"><b class="ico_6"></b></th>
        <th width="25px"><b class="ico_7"></b></th>
        <th width="25px"><b class="ico_8"></b></th>
        <th width="25px"><b class="ico_9"></b></th>
        <th width="25px"><b class="ico_10"></b></th>
        <th width="25px"><b class="ico_11"></b></th>
        <th width="25px"><b class="ico_12"></b></th>
        <th width="25px"><b class="ico_13"></b></th>
        <th width="100px">{ADM_RkL_Action}</th>
        <th width="200px"><b class="dash memCount">{ADM_RkL_UsersCount}</b></th>
    </tr>
    <form action="" method="post" class="sendForm">
        <input type="hidden" name="action" class="change" value=""/>
        {RanksRows}
        <tr>
            <th colspan="16" class="pad5">
                <input type="button" class="bold chgButton" value="{ADM_ChangeGeneralButton}"/>
            </th>
        </tr>
    </form>
    <tr>
        <td class="c" colspan="16">{ADM_RankList_AddNew}</td>
    </tr>
    <form action="" method="post">
        <input type="hidden" name="action" value="add"/>
        <tr class="mark">
            <th><input type="text" name="newName" /></th>
            <th{DisableInfo1}><input type="checkbox" {CBox_1}/></th>
            <th{DisableInfo2}><input type="checkbox" {CBox_2}/></th>
            <th{DisableInfo3}><input type="checkbox" {CBox_3}/></th>
            <th{DisableInfo4}><input type="checkbox" {CBox_4}/></th>
            <th{DisableInfo5}><input type="checkbox" {CBox_5}/></th>
            <th{DisableInfo6}><input type="checkbox" {CBox_6}/></th>
            <th{DisableInfo7}><input type="checkbox" {CBox_7}/></th>
            <th{DisableInfo8}><input type="checkbox" {CBox_8}/></th>
            <th{DisableInfo9}><input type="checkbox" {CBox_9}/></th>
            <th{DisableInfo10}><input type="checkbox" {CBox_10}/></th>
            <th{DisableInfo11}><input type="checkbox" {CBox_11}/></th>
            <th{DisableInfo12}><input type="checkbox" {CBox_12}/></th>
            <th{DisableInfo13}><input type="checkbox" {CBox_13}/></th>
            <th><input type="submit" class="bold" value="{ADM_RkL_AddRank}"/></th>
            <th>-</th>
        </tr>
    </form>
    <tr>
        <td class="c" colspan="16">(<a href="alliance.php?mode=admin">&#171; {GoBack}</a>)</td>
    </tr>
</table>
