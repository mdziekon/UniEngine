<style>.bgimg{background:url({skinpath}img/bg1.gif);}</style>
<br/>
<table width="130" cellspacing="0" cellpadding="0" align="center">
    <tr>
        <td style="border-top: 1px #545454 solid" class="center">
            <b style="border-bottom: dotted 1px;">{servername}</b><br/>
            <a onmouseover="this.style.background = 'none'" style="padding-left: 0;" class="red" title="{ChangeLogTitle}" href="changelog.php">{GameVersion} (Rev. {GameBuild})</a>
        </td>
    </tr>
    <tr><td class="center bgimg">{devlp}</td></tr>
    <tr><td><a href="overview.php" title="{OverviewTitle}">{Overview}</a></td></tr>
    <tr><td class="wht"></td></tr>
    <tr><td><a href="buildings.php" title="{BuildingsTitle}">{Buildings}</a></td></tr>
    <tr><td><a href="buildings.php?mode=research" title="{LabTitle}">{Research}</a></td></tr>
    <tr><td><a href="buildings.php?mode=fleet" title="{ShipyardTitle}">{Shipyard}</a></td></tr>
    <tr><td><a href="buildings.php?mode=defense" title="{DefenseTitle}">{Defense}</a></td></tr>
    <tr><td class="wht"></td></tr>
    <tr><td><a href="resources.php" title="{ResourcesTitle}">{Resources}</a></td></tr>
    <tr><td class="center bgimg">{navig}</td></tr>
    <tr><td><a href="fleet.php" title="{FleetTitle}">{Fleet}</a></td></tr>
    <tr><td><a href="alliance.php" title="{AllyTitle}" class="fl">{Alliance}</a>{LM_Insert_AllyChatLink}</td></tr>
    <tr><td>
        <a href="messages.php" title="{MsgTitle}" id="lm_msg" class="fl {Messages_Color}">{Messages} {Messages_AddCounter}</a>
        <a class="fr" style="background: url('{skinpath}img/m.gif') no-repeat; padding-left: 8px; margin-right: 2px;" href="messages.php?mode=write" title="{MsgWriteTitle}">&nbsp;</a>
    </td>
    </tr>
    <tr><td><a href="infos.php?gid=43" title="{TeleportTitle}">{Teleport}</a></td></tr>
    <tr><td><a href="tasks.php" title="{TasksTitle}" class="lime">{Tasks}</a></td></tr>
    <tr><td class="center bgimg">{observ}</td></tr>
    <tr><td><a href="galaxy.php?mode=0" title="{GalaxyTitle}">{Galaxy}</a></td></tr>
    <tr><td><a href="empire.php" title="{EmpireTitle}">{Empire}</a></td></tr>
    <tr><td><a href="techtree.php" title="{TechTitle}">{Technology}</a></td></tr>
    <tr><td><a href="records.php" title="{RecordsTitle}">{Records}</a></td></tr>
    <tr><td><a href="search.php" title="{SearchTitle}">{Search}</a></td></tr>
    <tr><td class="center bgimg">{PremiumZone}</td></tr>
    <tr><td><a class="red" href="galacticshop.php"title="{GalacticShopTitle}">{GalacticShop}</a></td></tr>
    <tr><td><a class="red" href="aboutpro.php"title="{AboutProTitle}">{AboutPro}</a></td></tr>
    <tr><td><a class="orange" href="merchant.php" title="{MerchantTitle}">&#187; {MerchantLink}</a></td></tr>
    <tr><td><a class="orange" href="disassembler.php" title="{DisassemblerTitle}">&#187; {Disassembler}</a></td></tr>
    <tr><td><a class="orange" href="attackslist.php" title="{AttacksListTitle}">&#187; {AttacksList}</a></td></tr>
    <tr><td><a class="red" href="officers.php"title="{OfficersTitle}">{Officers}</a></td></tr>
    <tr><td class="center bgimg">{Simulators}</td></tr>
    <tr><td><a class="orange" href="simulator.php" title="{SimulatorTitle}">{Simulator}</a></td></tr>
    <tr><td><a class="orange" href="rocket_simulator.php" title="{RocketSimulatorTitle}">{RocketSimulator}</a></td></tr>
    <tr><td><a class="orange" href="moon_sim.php" title="{MoonSimulatorTitle}">{MoonSimulator}</a></td></tr>
    <tr><td class="center bgimg">{commun}</td></tr>
    <tr><td><a href="declaration.php" title="{AddDeclareTitle}">{multi}</a></td></tr>
    <tr><td><a href="ref_table.php" class="orange" title="{RefTableTitle}">{RefTable}</a></td></tr>
</table>
