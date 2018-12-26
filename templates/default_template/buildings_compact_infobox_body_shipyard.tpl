<tbody id="nfoEl_{ElementID}" class="hide">
    <tr>
        <td class="infoImg shipImg">
            <a href="infos.php?gid={ElementID}"><img src="{SkinPath}gebaeude/{ElementID}.gif" class="infoImg shipImg"/></a>
        </td>
        <td class="infoDes">
            <a href="infos.php?gid={ElementID}"><b class="infoDesName">{ElementName}</b></a> ({InfoBox_Count}: {ElementCount})<br /><br /><span class="infoDesc">{Desc}</span>
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
                <b class="infoRLab">{InfoBox_MaxConstructible}:</b> <span class="infoRVal" id="maxConst_{ElementID}">{MaxConstructible}</span><br />
                {AdditionalNfo}
            </div>
        </td>
    </tr>
</tbody>
