<div
    id="ssEl_{Data_ElementID}"
    class="ssDiv"
    title="<center>{Data_ElementName}<br/>{Subcomponent_UpgradeImpossibleReason}</center>"
>
    <img
        src="{Data_SkinPath}gebaeude/{Data_ElementID}.gif"
        class="ssImg"
    />

    {Subcomponent_DisableOverlay}

    <a
        class="ssDo {Data_UpgradeBtn_ColorClass} {Data_UpgradeBtn_HideClass}"
        href="{Data_UpgradeElementAction_LinkHref}"
    >
        <span class="inv">
            &nbsp;
        </span>
    </a>
    <div class="ssBg {Data_ElementBackground_ShipClass}">
        &nbsp;
    </div>
    <div class="ssLvl {Data_ElementState_ShipClass}">
        {Data_ElementCurrentState}
    </div>

    {Subcomponent_LevelModifier}
    {Subcomponent_Addon_CountableInputs}
</div>
