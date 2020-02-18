<?php

namespace UniEngine\Engine\Modules\Development\Utils\Research;

use UniEngine\Engine\Includes\Helpers\Common;
use UniEngine\Engine\Includes\Helpers\Users;
use UniEngine\Engine\Includes\Helpers\Planets;
use UniEngine\Engine\Includes\Helpers\World\Elements;


function fetchResearchNetworkStatus($user) {
    $researchLabElementID = 31;
    $researchLabElementKey = Elements\getElementKey($researchLabElementID);

    $userID = $user['id'];
    $userResearchNetworkSize = Users\getResearchNetworkSize($user);

    $query_GetOtherLabs = '';
    $query_GetOtherLabs .= "SELECT `id`, `buildQueue`, `{$researchLabElementKey}` ";
    $query_GetOtherLabs .= "FROM {{table}} ";
    $query_GetOtherLabs .= "WHERE `id_owner` = {$userID} AND `planet_type` = 1;";

    $dbResult_GetOtherLabs = doquery($query_GetOtherLabs, 'planets');

    if ($dbResult_GetOtherLabs->num_rows === 0) {
        return [
            'connectedLabs' => 0,
            'connectedLabsLevel' => 0,
            'totalLabsLevel' => 0,
            'labsCount' => 0,
            'planetsWithLabInStructuresQueue' => []
        ];
    }

    $labsLevels = [];
    $planetsWithLabInStructuresQueue = [];

    while ($labRowData = $dbResult_GetOtherLabs->fetch_assoc()) {
        $thisPlanetID = $labRowData['id'];
        $thisResearchLabLevel = $labRowData[$researchLabElementKey];

        $labsLevels[] = $thisResearchLabLevel;

        if (empty($labRowData['buildQueue'])) {
            continue;
        }

        $hasResearchLabInQueue = Planets\Queues\Structures\hasElementInQueue(
            $labRowData,
            $researchLabElementID
        );

        if (!$hasResearchLabInQueue) {
            continue;
        }

        $planetsWithLabInStructuresQueue[] = $thisPlanetID;
    }

    $labsLevels = array_filter(
        $labsLevels,
        function ($value) {
            return ($value > 0);
        }
    );

    rsort($labsLevels);

    $connectedLabsLevels = Common\Collections\firstN($labsLevels, $userResearchNetworkSize);

    return [
        'allLabsCount' => count($labsLevels),
        'allLabsLevel' => array_sum($labsLevels),
        'connectedLabsCount' => count($connectedLabsLevels),
        'connectedLabsLevel' => array_sum($connectedLabsLevels),
        'planetsWithLabInStructuresQueue' => $planetsWithLabInStructuresQueue
    ];
}

?>
