{PHPInject_commonJS_html}
<script>
$(document).ready(function()
{
    var PHPInjectedData = {
        resourcesState: {
            metal: {
                resourceName: "{Metal}",
                incomePerHour: "{TipIncome_Metal}",
                fullStoreInText: '{Metal_full_time}',
                storeStatusText: '{Metal_store_status}'
            },
            crystal: {
                resourceName: "{Crystal}",
                incomePerHour: "{TipIncome_Crystal}",
                fullStoreInText: '{Crystal_full_time}',
                storeStatusText: '{Crystal_store_status}'
            },
            deuterium: {
                resourceName: "{Deuterium}",
                incomePerHour: "{TipIncome_Deuterium}",
                fullStoreInText: '{Deuterium_full_time}',
                storeStatusText: '{Deuterium_store_status}'
            },
        },
        specialResourcesState: {
            energy: {
                resourceName: "{Energy}",
                unused: `{Energy_free}`,
                used: `{Energy_used}`,
                total: `{Energy_total}`
            }
        },

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

    window.PHPInject_topnav_data = PHPInjectedData;
    window.PHPInject_topnav_lang = {
        When_full_store: `{When_full_store}`,
        Store_Status: `{Store_Status}`,
    };

    var resourcesDetails = [
        {
            resourceKey: "metal",
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

    setInterval(
        function () {
            updateResourceCounters(
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
        },
        1000
    );
});
</script>
<script src="dist/js/resourceUpdate.cachebuster-1566165656693.min.js"></script>
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
                    <td class="w145"></td>
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
