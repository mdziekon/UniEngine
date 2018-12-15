<?php

$FIP = $_EnginePath.'includes/functions/';

// Essential Functions
include($FIP.'CheckUserSessionCookie.php');
include($FIP.'CheckUserSession.php');
include($FIP.'TasksFunctions.php');

// Rest
if(!isset($_UseMinimalCommon) || $_UseMinimalCommon !== true)
{
    include($FIP.'HandlePlanetQueue.php');
    include($FIP.'HandlePlanetQueue_CreateQueueList.php');
    include($FIP.'HandlePlanetQueue_StructuresSetNext.php');
    include($FIP.'HandlePlanetQueue_TechnologySetNext.php');
    include($FIP.'HandlePlanetQueue_OnStructureBuildEnd.php');
    include($FIP.'HandlePlanetQueue_OnTechnologyEnd.php');
    include($FIP.'HandlePlanetUpdate.php');
    include($FIP.'HandlePlanetUpdate_MultiUpdate.php');
    include($FIP.'HandleShipyardQueue.php');
    include($FIP.'HandleFullUserUpdate.php');

    include($FIP.'FlyingFleetHandler.php');

    include($FIP.'SendSimpleMessage.php');
    include($FIP.'SendSimpleMassMessage.php');
    include($FIP.'SendSimpleMultipleMessages.php');
    include($FIP.'Cache_Message.php');

    include($FIP.'CheckPlanetUsedFields.php');
    include($FIP.'IsTechnologieAccessible.php');
    include($FIP.'GetBuildingTime.php');
    include($FIP.'GetBuildingPrice.php');
    include($FIP.'IsElementBuyable.php');

    include($FIP.'ShowTopNavigationBar.php');
    include($FIP.'SetSelectedPlanet.php');
    include($FIP.'PlanetResourceUpdate.php');
    include($FIP.'SortUserPlanets.php');
}

?>
