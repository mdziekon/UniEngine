<tbody
    id="nfoEl_{Data_ElementID}"
    class="hide"
>
    <tr>
        <td class="infoImg {Data_ElementImg_ShipClass}">
            <a href="infos.php?gid={Data_ElementID}">
                <img
                    src="{Data_SkinPath}gebaeude/{Data_ElementID}.gif"
                    class="infoImg {Data_ElementImg_ShipClass}"
                />
            </a>
        </td>
        <td class="infoDes">
            <a href="infos.php?gid={Data_ElementID}"><b class="infoDesName">{Data_ElementName}</b></a>
            ({Lang_InfoBox_CurrentState} {Data_ElementCurrentState}) {Subcomponent_LevelModifier}
            <br /><br />
            <span class="infoDesc">
                {Data_ElementDescription}
            </span>
        </td>
        <td class="center infoAction {Data_ActionBtns_HideClass}">
            <a
                class="{Data_UpgradeBtn_ColorClass} infoButton {Data_UpgradeBtn_HideClass}"
                href="{Data_UpgradeElementAction_LinkHref}"
            >
                <span class="infoButtonText">
                    {Lang_InfoBox_UpgradeAction} {Data_NextUpgradeLevelToQueue}
                </span>
            </a>
            <a
                id="bDest_{Data_ElementID}"
                class="{Data_DowngradeBtn_ColorClass} infoButton destButton {Data_DowngradeBtn_HideClass}"
                href="{Data_DowngradeElementAction_LinkHref}"
            >
                <span class="infoButtonText">
                    {Lang_InfoBox_DowngradeAction} {Data_NextDowngradeLevelToQueue}
                </span>
            </a>
        </td>
    </tr>
    <tr class="{Data_UpgradeInfo_HideClass}">
        <td colspan="3" class="infoDet">
            <div class="infoLeft">
                {Subcomponent_UpgradeRequirements}
            </div>
            <div class="infoRight">
                <b class="infoRLab">
                    {Lang_InfoBox_BuildTime}:
                </b>
                <span class="infoRVal">
                    {Subcomponent_BuildTime}
                </span>
                <br />
                {Subcomponent_AdditionalNfo}
            </div>
        </td>
    </tr>
    <tr class="{Data_UpgradeImpossible_HideClass}">
        <td
            colspan="3"
            class="infoDet detWarn center"
        >
            <b class="red">
                {Data_UpgradeImpossible_ReasonText}
            </b>
        </td>
    </tr>
</tbody>
