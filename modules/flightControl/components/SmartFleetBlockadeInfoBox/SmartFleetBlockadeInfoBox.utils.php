<?php

namespace UniEngine\Engine\Modules\FlightControl\Components\SmartFleetBlockadeInfoBox\Utils;

use UniEngine\Engine\Includes\Helpers\Common\Collections;

function fetchMostRecentBlockadeEntry() {
    $fetchQuery = (
        "SELECT " .
        "`ID`, `EndTime`, `BlockMissions`, `DontBlockIfIdle`, `Reason` " .
        "FROM {{table}} " .
        "WHERE " .
        "`Type` = 1 AND  " .
        "`StartTime` <= UNIX_TIMESTAMP() AND " .
        "(`EndTime` > UNIX_TIMESTAMP() OR `PostEndTime` > UNIX_TIMESTAMP()) " .
        "ORDER BY  " .
        "`EndTime` DESC  " .
        "LIMIT 1 " .
        "; "
    );

    return doquery($fetchQuery, 'smart_fleet_blockade', true);
}

function getMissionsInfo($blockadeEntry, $lang) {
    global $_Vars_FleetMissions;

    $infoParts = [];

    if ($blockadeEntry['BlockMissions'] == '0') {
        $infoParts[] = $lang['sfb_Mission_All'];

        if ($blockadeEntry['DontBlockIfIdle'] == 1) {
            $infoParts[] = $lang['sfb_Mission_AggresiveDontBlockIdle'];
        }

        return implode('', $infoParts);
    }

    $blockedMissions = explode(',', $blockadeEntry['BlockMissions']);
    $blockedMissions = Collections\compact($blockedMissions);
    $allBlockedMissionsCount = count($blockedMissions);
    $civilBlockedMissionsCount = 0;
    $militaryBlockedMissionsCount = 0;

    $blockedMissionLabels = [];

    foreach ($blockedMissions as $missionId) {
        if (!in_array($missionId, $_Vars_FleetMissions['all'])) {
            continue;
        }

        $blockedMissionLabels[] = $lang['sfb_Mission__'.$missionId];

        if (in_array($missionId, $_Vars_FleetMissions['civil'])) {
            $civilBlockedMissionsCount += 1;
        } else {
            $militaryBlockedMissionsCount += 1;
        }
    }

    $hasOnlyAllCivil = (
        $civilBlockedMissionsCount == count($_Vars_FleetMissions['civil']) &&
        $civilBlockedMissionsCount == $allBlockedMissionsCount
    );
    $hasOnlyAllMilitary = (
        $militaryBlockedMissionsCount == count($_Vars_FleetMissions['military']) &&
        $militaryBlockedMissionsCount == $allBlockedMissionsCount
    );

    if ($hasOnlyAllCivil) {
        $infoParts[] = $lang['sfb_Mission_Civil'];
    }
    if ($hasOnlyAllMilitary) {
        $infoParts[] = $lang['sfb_Mission_Aggresive'];
    }
    if (
        !$hasOnlyAllCivil &&
        !$hasOnlyAllMilitary
    ) {
        $infoParts[] = sprintf(
            $lang['sfb_Mission_Other'],
            implode(', ', $blockedMissionLabels)
        );
    }

    if (
        $blockadeEntry['DontBlockIfIdle'] == 1 &&
        $militaryBlockedMissionsCount > 0
    ) {
        $infoParts[] = (
            ($civilBlockedMissionsCount > 0) ?
                $lang['sfb_Mission_AggresiveDontBlockIdle'] :
                $lang['sfb_Mission_DontBlockIdle']
        );
    }

    return implode('', $infoParts);
}

function getReason($blockadeEntry, $lang) {
    global $_EnginePath, $_GameConfig;

    if (empty($blockadeEntry['Reason'])) {
        return $lang['sfb_NoReason'];
    }

    $rawReason = $blockadeEntry['Reason'];
    $formattedReason = null;

    if ($_GameConfig['enable_bbcode'] == 1) {
        include_once("{$_EnginePath}/includes/functions/BBcodeFunction.php");

        $formattedReason = trim(nl2br(bbcode(image(strip_tags(str_replace("'", '&#39;', $rawReason), '<br><br/>')))));
    } else {
        $formattedReason = trim(nl2br(strip_tags($rawReason, '<br><br/>')));
    }

    return "\"{$formattedReason}\"";
}

?>
