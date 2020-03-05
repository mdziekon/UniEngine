<tr>
    <td class="l">
        <a
            href="infos.php?gid={Data_ElementID}"
            id="b_id_{Data_ElementID}"
        >
            <img
                src="{Data_SkinPath}gebaeude/{Data_ElementID}.gif"
                width="120"
                height="120"
                class="{Data_ElementImgClass}"
            />
        </a>
    </td>
    <td class="l">
        <a href="infos.php?gid={Data_ElementID}">
            {Data_ElementName}
        </a>
        {Subcomponent_CurrentStateLabel}
        <br/>
        {Data_ElementDescription}
        <br/>
        <br/>
        {Subcomponent_UpgradeLevelHeaderRow}
        {Subcomponent_UpgradeCostRow}
        {Subcomponent_UpgradeResourcesLeftoverRow}
        {Subcomponent_UpgradeTimeRow}
        <span class="{Data_ProductionChangeLine_HideClass}">
            <br/>
            <span>{Lang_ProductionChange}:</span>
            {Subcomponent_ProductionChange}
        </span>

        {Subcomponent_TechnologyRequirementsList}
    </td>
    <td class="k">
        {Subcomponent_UpgradeActionLink}
    </td>
</tr>
