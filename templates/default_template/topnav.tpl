{PHPInject_commonJS_html}
<script>
window.PHPInject_topnav_data = {
    specialResourcesState: {
        energy: {
            resourceName: "{Energy}",
            unused: {PHPInject_energy_unused},
            used: {PHPInject_energy_used},
            total: {PHPInject_energy_total}
        }
    },
    resourcesDetails: [
        {
            resourceKey: "metal",
            resourceName: `{Metal}`,
            isOnVacation: {PHPInject_isOnVacation},
            storage: {
                maxCapacity: Math.floor({PHPInject_resource_metal_storage_maxCapacity}),
                overflowCapacity: Math.floor({PHPInject_resource_metal_storage_overflowCapacity})
            },
            state: {
                initial: Math.floor({PHPInject_resource_metal_state_amount}),
                incomePerHour: Math.floor({PHPInject_resource_metal_state_incomePerHour}),
            }
        },
        {
            resourceKey: "crystal",
            resourceName: `{Crystal}`,
            isOnVacation: {PHPInject_isOnVacation},
            storage: {
                maxCapacity: Math.floor({PHPInject_resource_crystal_storage_maxCapacity}),
                overflowCapacity: Math.floor({PHPInject_resource_crystal_storage_overflowCapacity})
            },
            state: {
                initial: Math.floor({PHPInject_resource_crystal_state_amount}),
                incomePerHour: Math.floor({PHPInject_resource_crystal_state_incomePerHour}),
            }
        },
        {
            resourceKey: "deuterium",
            resourceName: `{Deuterium}`,
            isOnVacation: {PHPInject_isOnVacation},
            storage: {
                maxCapacity: Math.floor({PHPInject_resource_deuterium_storage_maxCapacity}),
                overflowCapacity: Math.floor({PHPInject_resource_deuterium_storage_overflowCapacity})
            },
            state: {
                initial: Math.floor({PHPInject_resource_deuterium_state_amount}),
                incomePerHour: Math.floor({PHPInject_resource_deuterium_state_incomePerHour}),
            }
        }
    ]
};
window.PHPInject_topnav_lang = {
    When_full_store: `{When_full_store}`,
    Store_Status: `{Store_Status}`,

    topnav_incomeperhour_symbol: `{topnav_incomeperhour_symbol}`,
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
</script>
<script src="dist/js/topnav.resources.cachebuster-1578869030582.min.js"></script>
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
                        <div class="amount_display" id="metal" style="color: {PHPData_resource_metal_state_amount_color}">
                            {PHPData_resource_metal_state_amount_value}
                        </div>
                    </td>
                    <td class="w145 tooltip-trigger" data-resource-key="crystal">
                        <div class="amount_display" id="crystal" style="color: {PHPData_resource_crystal_state_amount_color}">
                            {PHPData_resource_crystal_state_amount_value}
                        </div>
                    </td>
                    <td class="w145 tooltip-trigger" data-resource-key="deuterium">
                        <div class="amount_display" id="deut" style="color: {PHPData_resource_deuterium_state_amount_color}">
                            {PHPData_resource_deuterium_state_amount_value}
                        </div>
                    </td>
                    <td class="w145 tooltip-trigger" data-resource-key="energy">
                        {PHPData_resource_energy_unused_html}
                    </td>
                    <td class="w50"></td>
                    <td class="w145">
                        {PHPData_messages_unread_amount_html}
                    </td>
                    <td class="w220">
                        {PHPData_premiumresource_darkenergy_amount_html}
                    </td>
                </tr>
                <tr class="tdct">
                    <td class="w145 tooltip-target tooltip-trigger" data-resource-key="metal">
                        <div class="storage_display" style="color: {PHPData_resource_metal_state_amount_color}">
                            {PHPData_resource_metal_storage_maxCapacity_value}
                        </div>
                    </td>
                    <td class="w145 tooltip-target tooltip-trigger" data-resource-key="crystal">
                        <div class="storage_display" style="color: {PHPData_resource_crystal_state_amount_color}">
                            {PHPData_resource_crystal_storage_maxCapacity_value}
                        </div>
                    </td>
                    <td class="w145 tooltip-target tooltip-trigger" data-resource-key="deuterium">
                        <div class="storage_display" style="color: {PHPData_resource_deuterium_state_amount_color}">
                            {PHPData_resource_deuterium_storage_maxCapacity_value}
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
