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
.pad3 {
    padding: 3px !important;
}
.hide {
    display: none;
}
.inv {
    visibility: hidden;
}
.markSelect {
    background-color: #4E6797;
    border-color: #607BAF;
}
</style>
<script>
$(document).ready(function()
{
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
});
</script>
<br/>
<table width="650">
    <tr>
        <td class="c" id="funct_box"><b class="ceswitch collapse">{ADM_Title}</b><b style="float: right">(<a href="alliance.php">&#171; {GoBack}</a>)</b></td>
    </tr>
    <tr class="funct_box">
        <th><a href="?mode=admin">{ADM_FrontPage}</a></th>
    </tr>
    <tr class="funct_box {HideManageAllyData}">
        <th class="markSelect"><a href="?mode=admin&amp;edit=info">{ADM_AllyInfo}</a></th>
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

<br/>

<form action="" method="post">
    <input type="hidden" name="change" value="name" />
    <table width="650" class="{HideChangeName}">
        <tr class="{HideInfoBox_name}">
            <td class="c pad5 {InfoBox_color}" colspan="2">{InfoBox_name}</td>
        </tr>
        <tr class="inv {HideInfoBox_name}">
            <td></td>
        </tr>
        <tr>
            <td class="c" colspan="2" id="rename_box"><b class="ceswitch collapse">{ADM_ChangeName}</b></td>
        </tr>
        <tr class="rename_box">
            <th class="pad5" width="220px">{ADM_NewName} ({ADM_NewNameLimits})</th>
            <th>
                <input type="text" name="new_name" maxlength="35" style="width: 250px;"/>
            </th>
        </tr>
        <tr class="rename_box">
            <th colspan="2" class="pad3">
                <input type="submit" value="{ADM_ChangeNameButton}"/>
            </th>
        </tr>
    </table>
</form>

<form action="" method="post">
    <input type="hidden" name="change" value="tag" />
    <table width="650" class="{HideChangeTag}">
        <tr class="{HideInfoBox_tag}">
            <td class="c pad5 {InfoBox_color}" colspan="2">{InfoBox_tag}</td>
        </tr>
        <tr class="inv {HideInfoBox_tag}">
            <td></td>
        </tr>
        <tr>
            <td class="c" colspan="2" id="retag_box"><b class="ceswitch collapse">{ADM_ChangeTag}</b></td>
        </tr>
        <tr class="retag_box">
            <th class="pad5" width="220px">{ADM_NewTag} ({ADM_NewTagLimits})</th>
            <th>
                <input type="text" name="new_tag" maxlength="8" style="width: 100px;"/>
            </th>
        </tr>
        <tr class="retag_box">
            <th colspan="2" class="pad3">
                <input type="submit" value="{ADM_ChangeTagButton}"/>
            </th>
        </tr>
    </table>
</form>

<form action="" method="post" style="margin: 0px;">
    <input type="hidden" name="change" value="general" />
    <table width="650">
        <tr class="{HideInfoBox_general}">
            <td class="c pad5" colspan="2">{InfoBox_general}</td>
        </tr>
        <tr class="inv {HideInfoBox_general}">
            <td></td>
        </tr>
        <tr>
            <td class="c" colspan="2" id="general_box"><b class="ceswitch collapse">{ADM_ChangeGeneral}</b></td>
        </tr>
        <tr class="general_box">
            <th class="pad5" width="220px">{ADM_WebSite}</th>
            <th>
                <input type="text" name="website" style="width: 300px;" value="{CurrentWebsite}"/>
            </th>
        </tr>
        <tr class="general_box">
            <th class="pad5">{ADM_WebSiteReveal}</th>
            <th>
                <input type="checkbox" name="website_reveal" {CheckRevealWebsite}/>
            </th>
        </tr>
        <tr class="general_box">
            <th class="pad5">{ADM_LogoURL}</th>
            <th>
                <input type="text" name="logourl" style="width: 300px;" value="{CurrentLogoUrl}"/>
            </th>
        </tr>
        <tr class="general_box">
            <th colspan="2" class="pad3">
                <input type="submit" value="{ADM_ChangeGeneralButton}"/>
            </th>
        </tr>
    </table>
</form>
