<style>
.expand {
    background: url('images/expand.png') no-repeat 0pt 0pt;
}
.collapse {
    background: url('images/collapse.png') no-repeat 0pt 0pt;
}
.expand, .collapse {
    padding-left: 18px;
    cursor: pointer;
}
.mark {
    cursor: pointer;
}
.hide {
    display: none;
}
.markHover {
    background-color: #455B87;
    border-color: #526EA3;
}
.markSelect {
    background-color: #4E6797;
    border-color: #607BAF;
}
.saveAll, .saveOne, .handOver, .delAlly, .bold {
    font-weight: bold;
}
.helpreq {
    border-bottom: 1px white dashed;
    cursor: help;
}
.reqSetOpt {
    color: white !important;
}
.disNCOpt, .disabled {
    color: gray !important;
}
.markLink:hover {
    text-decoration: none;
}
</style>
<script>
var $_MaxLength_IntText = {Insert_MaxLength_IntText};
var $_MaxLength_ExtText = {Insert_MaxLength_ExtText};
var $_MaxLength_ReqText = {Insert_MaxLength_ReqText};
$(document).ready(function()
{
    var SaveOneText = {Mark01: '{ADM_SaveMark1}', Mark02: '{ADM_SaveMark2}', Mark03: '{ADM_SaveMark3}'};

    $('.collapsed').hide();
    $('.ceswitch').click(function()
    {
        if($(this).hasClass('collapse')){
            $('.'+$(this).parent().attr('id')+':not(.hide)').hide();
            $(this).removeClass('collapse');
            $(this).addClass('expand');
        } else if($(this).hasClass('expand')){
            $('.'+$(this).parent().attr('id')+':not(.hide)').show().removeClass('collapsed');
            $(this).removeClass('expand');
            $(this).addClass('collapse');
        }
    }).tipTip({maxWidth: 'auto', content: '{AFP_CollExp}', defaultPosition: 'right', delay: 500, edgeOffset: 8});

    $('.helpreq').tipTip({maxWidth: '250px', content: '{ADM_AllyOpen4ReqInfo}', delay: 0, edgeOffset: 10});
    $('.disNCOpt').tipTip({maxWidth: '250px', content: '{ADM_OnlyAdminCanSetThis}', delay: 0, edgeOffset: 10, defaultPosition: 'left'});
    $('[name=allyOpen]:disabled, [name=newComerRank]:disabled').addClass('disabled');

    $("[id^=Cont]").hide(0);
    $("#Mark{MarkSelect}").addClass('markSelect');
    $("#Cont{MarkSelect}").show(0);
    $(".mark").hover(
    function()
    {
        if(!$(this).hasClass('markSelect')){
            $(this).addClass('markHover');
        }
    },
    function()
    {
        $(this).removeClass('markHover');
    }).click(
    function()
    {
        $(".mark").each(function(){ $(this).removeClass('markSelect') });
        $("[id^=Cont]").hide(0).addClass('hide');
        $("#"+$(this).attr('id').replace('Mark', 'Cont')).show(0).removeClass('hide');
        $(this).removeClass('markHover').addClass('markSelect');
        $('[name=saveonly]').val($(this).attr('id'));
        $('.saveOne').val(SaveOneText[$(this).attr('id')]);
        return false;
    });
    $('.markLink').click(function()
    {
        $(this).parent().click();
        return false;
    });

    $('[name=ext_text], [name=int_text], [name=req_text]').keydown(function()
    {
        var TextLength = $(this).val().length;
        var $_MaxLength = 0;
        var CntChars = null;
        if($(this).attr('name') == 'ext_text')
        {
            $_MaxLength = $_MaxLength_ExtText;
            CntChars = $('#cntChars1');
        }
        else if($(this).attr('name') == 'int_text')
        {
            $_MaxLength = $_MaxLength_IntText;
            CntChars = $('#cntChars2');
        }
        else if($(this).attr('name') == 'req_text')
        {
            $_MaxLength = $_MaxLength_ReqText;
            CntChars = $('#cntChars3');
        }
        if(TextLength > $_MaxLength)
        {
            $(this).val($(this).val().substr(0, $_MaxLength));
            TextLength = $_MaxLength;
        }
        CntChars.html(TextLength);
    }).keyup(function()
    {
        $(this).keydown();
    }).change(function()
    {
        $(this).keydown();
    });
    $('[name=ext_text], [name=int_text], [name=req_text]').keydown();

    $('a[class^="clear_"]').click(function()
    {
        $('textarea.'+$(this).attr('class')).val('').keydown();
        return false;
    });
    $('.saveOne').click(function()
    {
        $('[name=mode]').val('saveOne');
        return true;
    });
    $('#delAlly').submit(function()
    {
        return confirm('{ADM_SureWantDelete}');
    });

    if('{HideTextsSet}' != 'hide')
    {
        $('#{GetLastMark}').click();
    }
});
</script>
<br/>
<table width="650">
    <tr>
        <td class="c" id="funct_box"><b class="ceswitch collapse">{ADM_Title}</b><b class="fr">(<a href="alliance.php">&#171; {GoBack}</a>)</b></td>
    </tr>
    <tr class="funct_box">
        <th class="markSelect"><a href="?mode=admin">{ADM_FrontPage}</a></th>
    </tr>
    <tr class="funct_box {HideManageAllyData}">
        <th><a href="?mode=admin&amp;edit=info">{ADM_AllyInfo}</a></th>
    </tr>
    <tr class="funct_box {HideManageMemList}">
        <th><a href="?mode=admin&amp;edit=members">{ADM_MembersList}</a></th>
    </tr>
    <tr class="funct_box {HideManageRanks}">
        <th><a href="?mode=admin&amp;edit=ranks">{ADM_ChangeRanks}</a></th>
    </tr>
    <tr class="funct_box {HideLookReq}">
        <th><a href="?mode=admin&amp;edit=reqlist">{ADM_ReqList}</a></th>
    </tr>
</table>
<form action="" method="post" style="margin: 1px; margin-bottom: 0px;">
    <input type="hidden" name="change" value="texts"/>
    <input type="hidden" name="saveonly" value=""/>
    <input type="hidden" name="mode" value="saveAll"/>
    <table width="650">
        <tr class="{HideInfoBox}">
            <td class="c pad5 lime" colspan="3">{Info_Box}</td>
        </tr>
        <tr class="inv {HideInfoBox}">
            <td></td>
        </tr>
        <tr class="{HideWarnBox}">
            <td class="c pad5 orange" colspan="3">{Warn_Box}</td>
        </tr>
        <tr class="inv {HideWarnBox}">
            <td></td>
        </tr>

        <tr class="{HideTextsSet}">
            <td class="c" colspan="3" id="texts_box"><b class="ceswitch collapse">{ADM_Texts}</b></td>
        </tr>
        <tr class="texts_box {HideTextsSet}">
            <th class="mark pad5" id="Mark01" width="33%"><a href="" class="markLink">{ADM_ExternalText}</a></th>
            <th class="mark pad5" id="Mark02" width="33%"><a href="" class="markLink">{ADM_InternalText}</a></th>
            <th class="mark pad5" id="Mark03" width="33%"><a href="" class="markLink">{ADM_RequestText}</a></th>
        </tr>
        <tbody class="texts_box {HideTextsSet}" id="Cont01">
            <tr>
                <th class="c pad5" colspan="3"><b id="cntChars1">0</b> / {Insert_MaxLength_ExtText} {ADM_Characters} (<a href="" class="clear_1">{ADM_Clear}</a>)</th>
            </tr>
            <tr>
                <th colspan="3">
                    <textarea class="clear_1" name="ext_text" rows="5" cols="55">{Ext_Text}</textarea>
                </th>
            </tr>
        </tbody>
        <tbody class="texts_box {HideTextsSet}" id="Cont02">
            <tr>
                <th class="c pad5" colspan="3"><b id="cntChars2">0</b> / {Insert_MaxLength_IntText} {ADM_Characters} (<a href="" class="clear_2">{ADM_Clear}</a>)</th>
            </tr>
            <tr>
                <th colspan="3">
                    <textarea class="clear_2" name="int_text" rows="5" cols="55">{Int_Text}</textarea>
                </th>
            </tr>
        </tbody>
        <tbody class="texts_box {HideTextsSet}" id="Cont03">
            <tr>
                <th class="c pad5" colspan="3"><b id="cntChars3">0</b> / {Insert_MaxLength_ReqText} {ADM_Characters} (<a href="" class="clear_3">{ADM_Clear}</a>)</th>
            </tr>
            <tr>
                <th colspan="3">
                    <textarea class="clear_3" name="req_text" rows="5" cols="55">{Req_Text}</textarea>
                </th>
            </tr>
        </tbody>
        <tr class="texts_box {HideTextsSet}">
            <th colspan="3" class="pad5">
                <input type="submit" class="saveAll" value="{ADM_SaveAll}"/>
                <input type="submit" class="saveOne" value="{ADM_SaveOnly}"/>
            </th>
        </tr>
    </table>
</form>

<form action="" method="post" style="margin: 1px; margin-top: 0px;">
    <input type="hidden" name="change" value="reqset"/>
    <table width="650">
        <tr>
            <td class="c" id="reqset_box" colspan="2"><b class="ceswitch {CollapseReqSetButton}">{ADM_ReqSettings}</b></td>
        </tr>
        <tbody class="reqset_box {CollapseReqSet}">
            <tr>
                <th class="pad5" width="33%"><b class="helpreq">{ADM_AllyOpen4Req}</b></th>
                <th class="pad5">
                    <select style="width: 100px; text-align: center;" name="allyOpen" {DisableReqSetInputs}>
                        <option value="1" class="reqSetOpt" {AcceptReq_Select}>{ADM_AllyOpen_Accept}</option>
                        <option value="0" class="reqSetOpt" {BlockReq_Select}>{ADM_AllyOpen_Block}</option>
                    </select>
                </th>
            </tr>
            <tr>
                <th class="pad5">{ADM_NewComerRank}</th>
                <th class="pad5">
                    <select style="width: 200px; text-align: center;" name="newComerRank" {DisableReqSetInputs} {DisableReqSetRanks}>
                        {NewComersRankRows}
                    </select>
                </th>
            </tr>
            <tr>
                <th colspan="2" class="pad5">
                    <input type="submit" class="bold" value="{ADM_ChangeGeneralButton}"/>
                </th>
            </tr>
        </tbody>
    </table>
</form>

<table width="650" class="{HideHandOver}">
    <tr>
        <td class="c" id="handover_box"><b class="ceswitch expand">{ADM_HandOver}</b></td>
    </tr>
    <tr class="handover_box collapsed">
        <th class="pad5">
            <form action="?mode=admin&edit=handover" method="post" style="margin: 0px">
                <input type="submit" class="handOver" value="{ADM_SelectNewLeader}" />
            </form>
        </th>
    </tr>
</table>

<table width="650" class="{HideDeleteAlly}">
    <tr>
        <td class="c" id="delally_box"><b class="ceswitch expand">{ADM_DeleteAlly}</b></td>
    </tr>
    <tr class="delally_box collapsed">
        <th class="pad5">
            <form id="delAlly" action="?mode=admin&edit=delete" method="post" style="margin: 0px">
                <input type="submit" class="delAlly" value="{ADM_Click2Delete}" />
            </form>
        </th>
    </tr>
</table>
