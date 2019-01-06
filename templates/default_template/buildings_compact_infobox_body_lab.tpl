<tbody id="nfoEl_{ElementID}" class="hide">
    <tr>
        <td class="infoImg">
            <a href="infos.php?gid={ElementID}"><img src="{SkinPath}gebaeude/{ElementID}.gif" class="infoImg"/></a>
        </td>
        <td class="infoDes">
            <a href="infos.php?gid={ElementID}"><b class="infoDesName">{ElementName}</b></a> ({InfoBox_Level} {ElementRealLevel}){LevelModifier}<br /><br /><span class="infoDesc">{Desc}</span>
        </td>
        <td class="center infoAction">
            <a class="{BuildButtonColor} infoButton {HideBuildButton}" href="?mode=research&amp;cmd=search&amp;tech={ElementID}"><span class="infoButtonText">{InfoBox_Build} {BuildLevel}</span></a>
        </td>
    </tr>
    <tr class="{HideBuildInfo}">
        <td colspan="3" class="infoDet">
            <div class="infoLeft">
                {ElementRequirementsHeadline}
                <div class="infoResReq {HideResReqDiv}">{ElementPriceDiv}</div>
                <div class="infoTechReq">{ElementTechDiv}</div>
            </div>
            <div class="infoRight">
                <b class="infoRLab">{InfoBox_BuildTime}:</b> <span class="infoRVal">{BuildTime}</span><br />
                {AdditionalNfo}
            </div>
        </td>
    </tr>
    <tr class="{HideBuildWarn}">
        <td colspan="3" class="infoDet detWarn center">
            <b class="{BuildWarn_Color}">{BuildWarn_Text}</b>
        </td>
    </tr>
</tbody>
