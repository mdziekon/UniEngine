<tr>
    <th>{FleetNo}</th>
    <th>
        <b class="{FleetMissionColor}">{FleetMission}</b>
        {ACSOwner}
        <br/>
        <b class="FBeh" title="{FleetBehaviour}">{FleetBehaviourTxt}</b>
    </th>
    <th title="<table style='width: 100%;'><tr><th class='help_th help_left' colspan='2'><u>fl_fleetinfo_ships</u></th></tr>{FleetDetails}{FleetResInfo}{FleetAddShipsInfo}</table>" class="fInfo">
        {FleetCount}
    </th>
    <th>
        <a class="orange" href="galaxy.php?mode=3&galaxy={FleetOriGalaxy}&system={FleetOriSystem}&planet={FleetOriPlanet}">[{FleetOriGalaxy}:{FleetOriSystem}:{FleetOriPlanet}]</a><b class="{FleetOriType}"></b>
        <br/>{FleetOriStart}
    </th>
    <th>
        <a class="orange" href="galaxy.php?mode=3&galaxy={FleetDesGalaxy}&system={FleetDesSystem}&planet={FleetDesPlanet}">[{FleetDesGalaxy}:{FleetDesSystem}:{FleetDesPlanet}]</a><b class="{FleetDesType}"></b>
        <br/>{FleetDesArrive}
    </th>
    <th>
        <span class="orange">{FleetEndTime}</span>
    </th>
    <th class="pad5">
        <b{FleetHideTargetTime}><b class="flLe">fl_flytargettime</b>{FleetFlyTargetTime}<br /></b>
        <b{FleetHideStayTime}><b class="flLe">fl_flystaytime</b>{FleetFlyStayTime}<br /></b>
        <b{FleetHideComeBackTime}><b class="flLe">fl_flygobacktime</b>{FleetFlyBackTime}<br /></b>
        <b{FleetHideRetreatTime}><b class="flLe">fl_flyretreattime</b>{FleetRetreatTime}<br /></b>
    </th>
    <th>
        {FleetOrders}
    </th>
</tr>
