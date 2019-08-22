{PHPInject_commonJS_html}
<script>
$(document).ready(function()
{
    var PHPInjectedData = {
        isOnVacation: {isOnVacation},

        JSPerHour_Metal: {JSPerHour_Metal},
        JSStore_Metal: {JSStore_Metal},
        JSStoreOverflow_Metal: {JSStoreOverflow_Metal},
        JSCount_Metal: {JSCount_Metal},

        JSPerHour_Crystal: {JSPerHour_Crystal},
        JSStore_Crystal: {JSStore_Crystal},
        JSStoreOverflow_Crystal: {JSStoreOverflow_Crystal},
        JSCount_Crystal: {JSCount_Crystal},

        JSPerHour_Deuterium: {JSPerHour_Deuterium},
        JSStore_Deuterium: {JSStore_Deuterium},
        JSStoreOverflow_Deuterium: {JSStoreOverflow_Deuterium},
        JSCount_Deuterium: {JSCount_Deuterium},
    };

    window.PHPInject_topnav_data = {
        specialResourcesState: {
            energy: {
                resourceName: "{Energy}",
                unused: {PHPInject_energy_unused},
                used: {PHPInject_energy_used},
                total: {PHPInject_energy_total}
            }
        },
    };
    window.PHPInject_topnav_lang = {
        When_full_store: `{When_full_store}`,
        Store_Status: `{Store_Status}`,

        income_minus: `{income_minus}`,
        income_vacation: `{income_vacation}`,
        income_no_production: `{income_no_production}`,
        income_full: `{full}`,

        Store_status_Overload: `{Store_status_Overload}`,
        Store_status_Full: `{Store_status_Full}`,
        Store_status_Empty: `{Store_status_Empty}`,
        Store_status_NearFull: `{Store_status_NearFull}`,
        Store_status_OK: `{Store_status_OK}`
    };

    var resourcesDetails = [
        {
            resourceKey: "metal",
            resourceName: `{Metal}`,
            isOnVacation: PHPInjectedData.isOnVacation,
            storage: {
                maxCapacity: Math.floor(PHPInjectedData.JSStore_Metal),
                overflowCapacity: Math.floor(PHPInjectedData.JSStoreOverflow_Metal)
            },
            state: {
                initial: Math.floor(PHPInjectedData.JSCount_Metal),
                incomePerHour: Math.floor(PHPInjectedData.JSPerHour_Metal),
            }
        },
        {
            resourceKey: "crystal",
            resourceName: `{Crystal}`,
            isOnVacation: PHPInjectedData.isOnVacation,
            storage: {
                maxCapacity: Math.floor(PHPInjectedData.JSStore_Crystal),
                overflowCapacity: Math.floor(PHPInjectedData.JSStoreOverflow_Crystal)
            },
            state: {
                initial: Math.floor(PHPInjectedData.JSCount_Crystal),
                incomePerHour: Math.floor(PHPInjectedData.JSPerHour_Crystal),
            }
        },
        {
            resourceKey: "deuterium",
            resourceName: `{Deuterium}`,
            isOnVacation: PHPInjectedData.isOnVacation,
            storage: {
                maxCapacity: Math.floor(PHPInjectedData.JSStore_Deuterium),
                overflowCapacity: Math.floor(PHPInjectedData.JSStoreOverflow_Deuterium)
            },
            state: {
                initial: Math.floor(PHPInjectedData.JSCount_Deuterium),
                incomePerHour: Math.floor(PHPInjectedData.JSPerHour_Deuterium),
            }
        }
    ];

    var initialStateTimestamp = Date.now();
    var $parentEl = $("#topnav_resources");
    var countersCache = buildResourceUpdaterCache({
        resources: resourcesDetails
    });

    const resourceTooltips = resourcesDetails.map((resourceDetails) => {
        const resourceKey = resourceDetails.resourceKey;

        const tooltip = new ResourceTooltip({
            resourceKey,
            $parentEl,
            values: resourceDetails,
            bodyCreator: createProductionResourceTooltipBody
        });

        return {
            resourceKey,
            tooltip
        };
    });

    setInterval(
        function () {
            const result = updateResourceCounters(
                {
                    $parentEl,
                    timestamps: {
                        initial: initialStateTimestamp,
                        current: Date.now()
                    },
                    resources: resourcesDetails
                },
                countersCache
            );

            if (!result) {
                return;
            }

            result.forEach((resourceUpdateResult) => {
                if (!resourceUpdateResult) {
                    return;
                }

                const tooltip = resourceTooltips
                    .find((resourceTooltip) => resourceTooltip.resourceKey === resourceUpdateResult.resourceKey)
                    .tooltip;

                tooltip.updateValues({
                    state: {
                        current: resourceUpdateResult.currentAmount
                    }
                });
            });
        },
        1000
    );
});
</script>
<script src="dist/js/topnav.resources.cachebuster-1566487990541.min.js"></script>
<script src="dist/js/topnav.planet_selector.cachebuster-1566475997888.min.js"></script>
<link rel="stylesheet" type="text/css" href="dist/css/topNav.cachebuster-1546564327123.min.css"/>
<table id="topnav_resources">
    <tr>
        <td>
            <table>
                <tr>
                    <td><img src="{skinpath}planeten/small/s_{image}.jpg" id="plImg"/></td>
                    <td valign="middle" align="center">
                        <select id="planet" class="plSel plList" autocomplete="off">{planetlist}</select><br/>
                        <input id="prevPl" class="plSel plBut" type="button" value="<<" title="{PrevPlanet}"/>
                        <input id="plType" class="plSel {Insert_TypeChange_Hide}" type="button" data-id="{Insert_TypeChange_ID}" value="{Insert_TypeChange_Sign}" title="{Insert_TypeChange_Title}"/>
                        <input id="nextPl" class="plSel plBut" type="button" value=">>" title="{NextPlanet}"/>
                    </td>
                </tr>
            </table>
        </td>
        <td>
            <table id="resTopNav" border="0" cellpadding="0" cellspacing="0">
                <tr class="tdct">
                    <td class="w145 tooltip-hook tooltip-trigger" data-resource-key="metal">
                        <a href="resources.php">
                            <img src="{skinpath}images/metall.gif"/>
                        </a>
                    </td>
                    <td class="w145 tooltip-hook tooltip-trigger" data-resource-key="crystal">
                        <a href="resources.php">
                            <img src="{skinpath}images/kristall.gif"/>
                        </a>
                    </td>
                    <td class="w145 tooltip-hook tooltip-trigger" data-resource-key="deuterium">
                        <a href="resources.php">
                            <img src="{skinpath}images/deuterium.gif"/>
                        </a>
                    </td>
                    <td class="w145 tooltip-hook tooltip-trigger" data-resource-key="energy">
                        <a href="resources.php">
                            <img src="{skinpath}images/energie.gif"/>
                        </a>
                    </td>
                    <td class="w50"></td>
                    <td class="w145">
                        <a href="messages.php">
                            <img src="{skinpath}images/message.gif" class="resImg"/>
                        </a>
                    </td>
                    <td class="w220">
                        <img src="{skinpath}images/darkenergy.gif" class="resImg"/>
                    </td>
                </tr>
                <tr class="tdct">
                    <td class="w145 resTD tooltip-trigger" data-resource-key="metal">
                        {Metal}
                    </td>
                    <td class="w145 resTD tooltip-trigger" data-resource-key="crystal">
                        {Crystal}
                    </td>
                    <td class="w145 resTD tooltip-trigger" data-resource-key="deuterium">
                        {Deuterium}
                    </td>
                    <td class="w145 resTD tooltip-trigger" data-resource-key="energy">
                        {Energy}
                    </td>
                    <td class="w50"></td>
                    <td class="w145">
                        <a href="messages.php">
                            <u><i><b>{Message}</b></i></u>
                        </a>
                    </td>
                    <td class="w220">
                        <u><i><b>{DarkEnergy}</b></i></u>
                    </td>
                </tr>
                <tr class="tdct">
                    <td class="w145 tooltip-trigger" data-resource-key="metal">
                        <div class="amount_display" id="metal" style="color: {ShowCountColor_Metal}">
                            {ShowCount_Metal}
                        </div>
                    </td>
                    <td class="w145 tooltip-trigger" data-resource-key="crystal">
                        <div class="amount_display" id="crystal" style="color: {ShowCountColor_Crystal}">
                            {ShowCount_Crystal}
                        </div>
                    </td>
                    <td class="w145 tooltip-trigger" data-resource-key="deuterium">
                        <div class="amount_display" id="deut" style="color: {ShowCountColor_Deuterium}">
                            {ShowCount_Deuterium}
                        </div>
                    </td>
                    <td class="w145 tooltip-trigger" data-resource-key="energy">
                        {Energy_free}
                    </td>
                    <td class="w50"></td>
                    <td class="w145">
                        {ShowCount_Messages}
                    </td>
                    <td class="w220">
                        {ShowCount_DarkEnergy}
                    </td>
                </tr>
                <tr class="tdct">
                    <td class="w145 tooltip-target tooltip-trigger" data-resource-key="metal">
                        <div class="storage_display" style="color: {ShowStoreColor_Metal}">
                            {ShowStore_Metal}
                        </div>
                    </td>
                    <td class="w145 tooltip-target tooltip-trigger" data-resource-key="crystal">
                        <div class="storage_display" style="color: {ShowStoreColor_Crystal}">
                            {ShowStore_Crystal}
                        </div>
                    </td>
                    <td class="w145 tooltip-target tooltip-trigger" data-resource-key="deuterium">
                        <div class="storage_display" style="color: {ShowStoreColor_Deuterium}">
                            {ShowStore_Deuterium}
                        </div>
                    </td>
                    <td class="w145 tooltip-target" data-resource-key="energy"></td>
                    <td class="w50"></td>
                    <td class="w145"></td>
                    <td class="w220">
                        <a href="galacticshop.php">
                            <b class="red">&#187; {GoToShop} &#171;</b>
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
