<?php

use \UniEngine\Utils\OneOffs as OneOffs;

/**
 * Description:
 *  - Finds all users with completed, yet stuck tasks and releases these tasks
 *    (marks them as "locked", which should unlock them on next user's visit).
 *
 */
class Script_20200424_031431 implements OneOffs\Interfaces\Script {
    public function execute() {
        global $GlobalParsedTasks, $UserTasksUpdate;

        $GlobalParsedTasks = [];
        $UserTasksUpdate = [];

        $this->stubCommon();

        $queryResult_userRows = doquery("SELECT * FROM {{table}};", 'users');

        while ($userRow = $queryResult_userRows->fetch_assoc()) {
            $userID = $userRow['id'];

            Tasks_CheckUservar($userRow);

            $GlobalParsedTasks[$userID] = $userRow;
            $UserTasksUpdate[$userID] = $this->seedUserTasksUpdateObject();

            Handler_UserTasksUpdate();
        }
    }

    private function stubCommon() {
        global $_MemCache, $_GameConfig, $_User, $_Lang, $_DBLink, $_EnginePath;

        define('INSIDE', true);

        $_EnginePath = './';

        ini_set('default_charset', 'UTF-8');

        $_GameConfig = [];
        $_User = [
            'id' => null,
        ];
        $_Lang = [];
        $_DBLink = '';

        include($_EnginePath . 'common.minimal.php');
        include($_EnginePath . 'includes/constants.php');
        include($_EnginePath . 'common/_includes.php');
        include($_EnginePath . 'includes/functions.php');
        include($_EnginePath . 'includes/unlocalised.php');
        include($_EnginePath . 'includes/helpers/_includes.php');
        include($_EnginePath . 'includes/ingamefunctions.php');
        include($_EnginePath . 'class/UniEngine_Cache.class.php');

        $this->includeVars();

        include($_EnginePath . 'includes/db.php');
        include($_EnginePath . 'includes/strings.php');
        include($_EnginePath . 'includes/per_module/common/_includes.php');

        $_MemCache = new UniEngine_Cache();

        // Load game configuration
        $_GameConfig = loadGameConfig([
            'cache' => &$_MemCache,
        ]);
    }

    private function includeVars() {
        global $_EnginePath;

        global $_Vars_FleetMissions;
        global $_Vars_AllyRankLabels;
        global $_Vars_TechSpeedModifiers;
        global $_Vars_PremiumBuildings;
        global $_Vars_PremiumBuildingPrices;
        global $_Vars_IndestructibleBuildings;
        global $_Vars_MaxElementLevel;
        global $_Vars_BuildingsFixedBuildTime;
        global $_Vars_ProAccountData;
        global $_Vars_CombatData;
        global $_Vars_ElementCategories;
        global $_Vars_CombatUpgrades;
        global $_Vars_Officers;
        global $_Vars_Prices;
        global $_Vars_Requirements;
        global $_Vars_GameElements;
        global $_Vars_ResProduction;
        global $_Vars_ResStorages;
        global $_Vars_TasksData;

        include($_EnginePath . 'includes/vars.php');
    }

    private function seedUserTasksUpdateObject() {
        global $_Vars_TasksData;

        $updateObject = [
            'done' => [],
        ];

        foreach ($_Vars_TasksData as $categoryID => $categoryDetails) {
            $updateObject['done'][$categoryID] = [];

            foreach ($categoryDetails['tasks'] as $taskID => $taskDetails) {
                $updateObject['done'][$categoryID][$taskID] = [];
            }
        }

        return $updateObject;
    }
}

?>
