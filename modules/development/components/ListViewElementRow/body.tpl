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
                class="buildImg"
            />
        </a>
    </td>
    <td class="l">
        <a href="infos.php?gid={Data_ElementID}">
            {Data_ElementName}
        </a>
        {Subcomponent_CurrentStateLabel}
        {Subcomponent_ProductionChange}
        <br/>
        {Data_ElementDescription}
        <br/>
        <br/>
        {Subcomponent_UpgradeLevelHeaderRow}
        {Subcomponent_UpgradeCostRow}
        {Subcomponent_UpgradeTimeRow}
        {Subcomponent_UpgradeResourcesLeftoverRow}

        {Subcomponent_TechnologyRequirementsList}
    </td>
    <td class="k">
        {Subcomponent_UpgradeActionLink}
    </td>
</tr>
