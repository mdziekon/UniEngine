<tr>
    <td class="l">
        <a
            href="infos.php?gid={ElementID}"
            id="b_id_{ElementID}"
        >
            <img
                src="{SkinPath}gebaeude/{ElementID}.gif"
                width="120"
                height="120"
                class="buildImg"
            />
        </a>
    </td>
    <td class="l">
        <a href="infos.php?gid={ElementID}">{ElementName}</a> ({InfoBox_Level} {ElementRealLevel})
        <br/>
        {Desc}
        <br/><br/>

        <span class="{Data_HideNextUpgradeLevelClass}">
            <b>[{InfoBox_Level}: {Data_NextUpgradeLevel}]</b>
            <br/>
        </span>

        <span class="{Data_HideResourceRequirements}">
            <span>{Requires}:</span>
            {Component_UpgradePriceList}
            <br/>
        </span>
        <span
            class="{Data_HideResourceRequirements}"
            style="color: #7f7f7f;"
        >
            <span>{Lang_ResourcesLeft}:</span>
            {Component_UpgradeRestResourcesList}
            <br/>
        </span>
        <span class="{Data_HideResourceRequirements}">
            <span>{ConstructionTime}:</span>
            {UpgradeTime}
            <br/>
        </span>
        <span class="{Data_HideProductionChangeLineClass}">
            <span>{Lang_ProductionChange}:</span>
            {Component_UpgradeProductionChangeList}
            <br/>
        </span>
        <span class="{Data_HideTechRequirementsClass}">
            <br/>
            {Component_TechnologicalRequirements}
        </span>
    </td>
    <td class="k">
        {Component_UpgradeButton}
    </td>
</tr>
