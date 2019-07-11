<?php

// Important functions
function ReadFromFile($filename)
{
    return @file_get_contents($filename);
}

function parsetemplate($template, $array)
{
    return preg_replace_callback(
        '#\{([a-z0-9\-_]*?)\}#Ssi',
        function ($matches) use ($array) {
            return (
                isset($array[$matches[1]]) ?
                $array[$matches[1]] :
                ""
            );
        },
        $template
    );
}

function gettemplate($templatename)
{
    global $_EnginePath;

    return ReadFromFile($_EnginePath . UNIENGINE_TEMPLATE_DIR . UNIENGINE_TEMPLATE_NAME . '/' . $templatename . '.tpl');
}

function getDefaultUniLang() {
    if (defined('UNI_DEFAULT_LANG')) {
        return UNI_DEFAULT_LANG;
    }

    return UNIENGINE_DEFAULT_LANG;
}

function getCurrentLang() {
    global $_User;

    $lang = getDefaultUniLang();

    if (
        isset($_User['lang']) &&
        $_User['lang'] != '' &&
        in_array($_User['lang'], UNIENGINE_LANGS_AVAILABLE)
    ) {
        $lang = $_User['lang'];
    }

    if (
        !isset($_User['lang']) &&
        isset($_COOKIE[UNIENGINE_VARNAMES_COOKIE_LANG]) &&
        in_array($_COOKIE[UNIENGINE_VARNAMES_COOKIE_LANG], UNIENGINE_LANGS_AVAILABLE)
    ) {
        $lang = $_COOKIE[UNIENGINE_VARNAMES_COOKIE_LANG];
    }

    return $lang;
}

function getCurrentLangISOCode() {
    return getCurrentLang();
}

function includeLang($filename, $Return = false)
{
    global $_EnginePath;

    if (!$Return) {
        global $_Lang;
    }

    $SelLanguage = getCurrentLang();

    include("{$_EnginePath}language/{$SelLanguage}/{$filename}.lang");

    if ($Return) {
        return $_Lang;
    }
}

function langFileExists($filename) {
    global $_EnginePath;

    $SelLanguage = getCurrentLang();

    $filepath = "{$_EnginePath}language/{$SelLanguage}/{$filename}.lang";

    return file_exists($filepath);
}

function getJSDatePickerTranslationLang() {
    $lang = getCurrentLang();

    $langMapping = [
        'en' => 'en-GB',
        'pl' => 'pl'
    ];

    return $langMapping[$lang];
}

// Fleet-related functions

//  Arguments:
//      - $origin (Object)
//          - galaxy (Number)
//          - system (Number)
//          - planet (Number)
//      - $destination (Object)
//          - galaxy (Number)
//          - system (Number)
//          - planet (Number)
//
function getFlightDistanceBetween($origin, $destination) {
    $galaxiesDiff = ($origin['galaxy'] - $destination['galaxy']);

    if ($galaxiesDiff != 0) {
        return (abs($galaxiesDiff) * 20000);
    }

    $systemsDiff = ($origin['system'] - $destination['system']);

    if ($systemsDiff != 0) {
        return ((abs($systemsDiff) * 5 * 19) + 2700);
    }

    $planetsDiff = ($origin['planet'] - $destination['planet']);

    if ($planetsDiff != 0) {
        return ((abs($planetsDiff) * 5) + 1000);
    }

    return 5;
}

function GetGameSpeedFactor()
{
    global $_GameConfig;

    return $_GameConfig['fleet_speed'] / 2500;
}

function getUsersTechLevel($techID, $user) {
    global $_Vars_GameElements;

    $userTechKey = $_Vars_GameElements[$techID];

    return $user[$userTechKey];
}

function getShipsEngines($shipID) {
    global $_Vars_Prices;

    if (empty($_Vars_Prices[$shipID]['engine'])) {
        return [];
    }

    return $_Vars_Prices[$shipID]['engine'];
}

function getShipsStorageCapacity($shipID) {
    global $_Vars_Prices;

    return $_Vars_Prices[$shipID]['capacity'];
}

function getShipsUsedEngineData($shipID, $user) {
    $engines = getShipsEngines($shipID);

    // The assumption here is that better engines come first.
    // If the engine's tech is not set, we assume that it's the only engine available.
    foreach ($engines as $engineIdx => $engineData) {
        if (!isset($engineData['tech'])) {
            return [
                'engineIdx' => $engineIdx,
                'data' => $engineData
            ];
        }

        $engineTechID = $engineData['tech'];
        $engineTechMinLevel = $engineData['minlevel'];
        $userTechLevel = getUsersTechLevel($engineTechID, $user);

        if ($userTechLevel >= $engineTechMinLevel) {
            return [
                'engineIdx' => $engineIdx,
                'data' => $engineData
            ];
        }
    }

    return [
        'engineIdx' => -1,
        'data' => null
    ];
}

function getUsersEngineSpeedTechModifier($engineTechID, $user) {
    global $_Vars_TechSpeedModifiers;

    $engineTechSpeedModifier = $_Vars_TechSpeedModifiers[$engineTechID];
    $userTechLevel = getUsersTechLevel($engineTechID, $user);

    return (1 + ($engineTechSpeedModifier * $userTechLevel));
}

function getShipsCurrentSpeed($shipID, $user) {
    $usedEngine = getShipsUsedEngineData($shipID, $user);

    if (!$usedEngine['data']) {
        return 0;
    }

    $engineData = $usedEngine['data'];

    if (!isset($engineData['tech'])) {
        return $engineData['speed'];
    }

    $engineTechID = $engineData['tech'];
    $engineSpeedTechModifier = getUsersEngineSpeedTechModifier($engineTechID, $user);

    // TODO: determine if the modifier should not be applied with a "base bias"
    // meaning that it starts "improving" it starting from the minimal tech level.
    return (
        $engineData['speed'] *
        $engineSpeedTechModifier
    );
}

function getFleetShipsSpeeds($fleetShips, $user) {
    $speedsPerShip = [];

    foreach ($fleetShips as $shipID => $_shipsCount) {
        $speedsPerShip[$shipID] = getShipsCurrentSpeed($shipID, $user);
    }

    return $speedsPerShip;
}

function getShipsCurrentConsumption($shipID, $user) {
    $usedEngine = getShipsUsedEngineData($shipID, $user);

    if (!$usedEngine['data']) {
        return 0;
    }

    return $usedEngine['data']['consumption'];
}

//  Arguments:
//      - $flightParams (Object)
//          - speedFactor (Number)
//          - distance (Number)
//          - maxShipsSpeed (Number)
//
function getFlightDuration($flightParams) {
    $serverFlightSpeedFactor = GetGameSpeedFactor();

    $flightSpeedFactor = $flightParams['speedFactor'];
    $flightMaxShipsSpeed = $flightParams['maxShipsSpeed'];
    $flightDistance = $flightParams['distance'];

    $duration = (
        (35000 / $flightSpeedFactor * sqrt($flightDistance * 10 / $flightMaxShipsSpeed) + 10) /
        $serverFlightSpeedFactor
    );

    return round($duration);
}

//  Arguments:
//      - $flightParams (Object)
//          - ships (Object<shipID, count>)
//          - distance (Number)
//          - duration (Number)
//      - $user (Object)
//
function getFlightTotalConsumption($flightParams, $user) {
    $serverFlightSpeedFactor = GetGameSpeedFactor();

    $flightShips = $flightParams['ships'];
    $flightDistance = $flightParams['distance'];
    $flightDuration = $flightParams['duration'];

    $totalConsumption = 0;

    foreach ($flightShips as $shipID => $shipsCount) {
        if ($shipsCount <= 0) {
            continue;
        }

        $shipSpeed = getShipsCurrentSpeed($shipID, $user);
        $shipConsumption = getShipsCurrentConsumption($shipID, $user);

        $finalSpeed = 35000 / ($flightDuration * $serverFlightSpeedFactor - 10) * sqrt($flightDistance * 10 / $shipSpeed);

        $allShipsBaseConsumption = ($shipConsumption * $shipsCount);

        $allShipsConsumption = $allShipsBaseConsumption * $flightDistance / 35000 * (($finalSpeed / 10) + 1) * (($finalSpeed / 10) + 1);

        $totalConsumption += $allShipsConsumption;
    }

    return (round($totalConsumption) + 1);
}

function GetStartAdressLink($FleetRow, $FleetType, $FromWindow = false)
{
    $Link = '';
    $Link .= "<a ".(($FromWindow === true) ? "onclick=\"opener.location = this.href; opener.focus(); return false;\"" : '')." href=\"galaxy.php?mode=3&galaxy={$FleetRow['fleet_start_galaxy']}&system={$FleetRow['fleet_start_system']}&planet={$FleetRow['fleet_start_planet']}\" class=\"{$FleetType}\" >";
    $Link .= "[{$FleetRow['fleet_start_galaxy']}:{$FleetRow['fleet_start_system']}:{$FleetRow['fleet_start_planet']}]</a>";
    return $Link;
}

function GetTargetAdressLink($FleetRow, $FleetType, $FromWindow = false)
{
    $Link = '';
    $Link .= "<a ".(($FromWindow === true) ? "onclick=\"opener.location = this.href; opener.focus(); return false;\"" : '')." href=\"galaxy.php?mode=3&galaxy={$FleetRow['fleet_end_galaxy']}&system={$FleetRow['fleet_end_system']}&planet={$FleetRow['fleet_end_planet']}\" class=\"{$FleetType}\" >";
    $Link .= "[{$FleetRow['fleet_end_galaxy']}:{$FleetRow['fleet_end_system']}:{$FleetRow['fleet_end_planet']}]</a>";
    return $Link;
}

function BuildHostileFleetPlayerLink($FleetRow, $FromWindow = false)
{
    global $_Lang, $_SkinPath;

    $Link = '';
    $Link .= $FleetRow['owner_name']." ";
    $Link .= "<a ".(($FromWindow === true) ? "onclick=\"opener.location = this.href; opener.focus(); return false;\"" : '')." href=\"messages.php?mode=write&uid={$FleetRow['fleet_owner']}\">";
    $Link .= "<img src=\"{$_SkinPath}/img/m.gif\" alt=\"{$_Lang['ov_message']}\" title=\"{$_Lang['ov_message']}\" border=\"0\"></a>";
    return $Link;
}

function CreatePlanetLink($Galaxy, $System, $Planet)
{
    $Link = '';
    $Link .= "<a href=\"galaxy.php?mode=3&galaxy={$Galaxy}&system={$System}&planet={$Planet}\">";
    $Link .= "[{$Galaxy}:{$System}:{$Planet}]</a>";
    return $Link;
}

function GetNextJumpWaitTime($CurMoon)
{
    global $_Vars_GameElements;

    $JumpGateLevel = $CurMoon[$_Vars_GameElements[43]];
    $LastJumpTime = $CurMoon['last_jump_time'];
    if($JumpGateLevel > 0)
    {
        $WaitBetweenJmp = 3600 * (1 / $JumpGateLevel);
        $NextJumpTime = $LastJumpTime + $WaitBetweenJmp;

        $Now = time();
        if($NextJumpTime >= $Now)
        {
            $RestWait = $NextJumpTime - $Now;
            $RestString = ' '.pretty_time($RestWait);
        }
        else
        {
            $RestWait = 0;
            $RestString = '';
        }
    }
    else
    {
        $RestWait = 0;
        $RestString = '';
    }
    $RetValue['string'] = $RestString;
    $RetValue['value'] = $RestWait;

    return $RetValue;
}

function CreateFleetPopupedFleetLink($FleetRow, $Texte)
{
    global $_Lang;

    $FleetArray = String2Array($FleetRow['fleet_array']);
    if(!empty($FleetArray))
    {
        foreach($FleetArray as $ShipID => $ShipCount)
        {
            $CreateTitle[] = "<tr><th class='flLabel sh'>{$_Lang['tech'][$ShipID]}:</th><th class='flVal'>".prettyNumber($ShipCount)."</th></tr>";
        }
    }
    if($FleetRow['fleet_resource_metal'] > 0 OR $FleetRow['fleet_resource_crystal'] > 0 OR $FleetRow['fleet_resource_deuterium'] > 0)
    {
        $CreateTitle[] = '<tr><th class=\'flRes\' colspan=\'2\'>&nbsp;</th></tr>';
        if($FleetRow['fleet_resource_metal'] > 0)
        {
            $CreateTitle[] = "<tr><th class='flLabel rs'>{$_Lang['Metal']}:</th><th class='flVal'>".prettyNumber($FleetRow['fleet_resource_metal'])."</th></tr>";
        }
        if($FleetRow['fleet_resource_crystal'] > 0)
        {
            $CreateTitle[] = "<tr><th class='flLabel rs'>{$_Lang['Crystal']}:</th><th class='flVal'>".prettyNumber($FleetRow['fleet_resource_crystal'])."</th></tr>";
        }
        if($FleetRow['fleet_resource_deuterium'] > 0)
        {
            $CreateTitle[] = "<tr><th class='flLabel rs'>{$_Lang['Deuterium']}:</th><th class='flVal'>".prettyNumber($FleetRow['fleet_resource_deuterium'])."</th></tr>";
        }
    }

    return '<a class="white flShips" title="<table style=\'width: 100%;\'>'.implode('', $CreateTitle).'</table>">'.$Texte.'</a>';
}

// String-related functions

// $Seconds (Number)
//      Time's seconds
// $ChronoType (Boolean)
//      Should the timer display time in a format supported by JS Chrono timers
// $Format (String)
//      Determines how to display the time. Should be a concatenated string of possible flags.
//      Possible flags:
//      - (Chrono format enabled)
//          - D
//              Display days, in full lang format
//          - d
//              Display days, in short lang format
//      - (Chrono format disabled)
//          - d
//              Display days, in short lang format
//          - h
//              Display hours, in short lang format
//          - m
//              Display minutes, in short lang format
//          - s
//              Display seconds, in short lang format
//
function pretty_time($Seconds, $ChronoType = false, $Format = false) {
    global $_Lang;

    $timePieces = [];

    $Seconds = floor($Seconds);
    $Days = floor($Seconds / TIME_DAY);
    $Seconds -= $Days * TIME_DAY;
    $Hours = floor($Seconds / 3600);
    $Seconds -= $Hours * 3600;
    $Minutes = floor($Seconds / 60);
    $Seconds -= $Minutes * 60;

    $hoursString = str_pad((string) $Hours, 2, '0', STR_PAD_LEFT);
    $minutesString = str_pad((string) $Minutes, 2, '0', STR_PAD_LEFT);
    $secondsString = str_pad((string) $Seconds, 2, '0', STR_PAD_LEFT);

    if ($ChronoType === false) {
        if (!$Format) {
            $Format = 'dhms';
        }

        $isPieceAllowed = [
            'days' => (strstr($Format, 'd') !== false),
            'hours' => (strstr($Format, 'h') !== false),
            'minutes' => (strstr($Format, 'm') !== false),
            'seconds' => (strstr($Format, 's') !== false)
        ];

        if ($Days > 0 && $isPieceAllowed['days']) {
            $timePieces[] = $_Lang['Chrono_PrettyTime']['longFormat']['days']($Days);
        }
        if ($isPieceAllowed['hours']) {
            $timePieces[] = $_Lang['Chrono_PrettyTime']['longFormat']['hours']($hoursString);
        }
        if ($isPieceAllowed['minutes']) {
            $timePieces[] = $_Lang['Chrono_PrettyTime']['longFormat']['minutes']($minutesString);
        }
        if ($isPieceAllowed['seconds']) {
            $timePieces[] = $_Lang['Chrono_PrettyTime']['longFormat']['seconds']($secondsString);
        }

        return implode(' ', $timePieces);
    }

    if ($Days > 0) {
        if (!$Format) {
            $Format = '';
        }

        $isPieceAllowed = [
            'daysFull' => (strstr($Format, 'D') !== false),
            'daysShort' => (strstr($Format, 'd') !== false)
        ];

        if ($isPieceAllowed['daysFull']) {
            $timePieces[] = $_Lang['Chrono_PrettyTime']['chronoFormat']['daysFull']($Days);
        } else if ($isPieceAllowed['daysShort']) {
            $timePieces[] = $_Lang['Chrono_PrettyTime']['chronoFormat']['daysShort']($Days);
        } else {
            $Hours += $Days * 24;

            $hoursString = str_pad((string) $Hours, 2, '0', STR_PAD_LEFT);
        }
    }

    $timePieces[] = "{$hoursString}:{$minutesString}:{$secondsString}";

    return implode(' ', $timePieces);
}

function pretty_time_hour($seconds, $NoSpace = false)
{
    $min = floor($seconds / 60 % 60);

    if($min != 0)
    {
        if($NoSpace)
        {
            $time = $min.'min';
        }
        else
        {
            $time = $min.'min ';
        }
    }
    else
    {
        $time = '';
    }

    return $time;
}

function prettyMonth($month, $variant = '0')
{
    global $_Lang;
    static $_PrettyMonthsLocaleLoaded = false;

    if (!$_PrettyMonthsLocaleLoaded) {
        includeLang('months');

        $_PrettyMonthsLocaleLoaded = true;
    }

    return $_Lang['months_variant'.$variant][($month-1)];
}

function prettyDate($format, $timestamp = false, $variant = '0')
{
    global $_Lang;
    static $_PrettyMonthsLocaleLoaded = false;

    if (!$_PrettyMonthsLocaleLoaded) {
        includeLang('months');

        $_PrettyMonthsLocaleLoaded = true;
    }

    if (isset($_Lang['__helpers'])) {
        $formatter = $_Lang['__helpers']['date_formatters'][$variant];

        return $formatter($format, $timestamp);
    }

    // DEPRECATED: should be replaced with lang specific formatters
    if (strstr($format, 'm') !== false) {
        $HasMonth = true;
        $format = str_replace('m', '{|_|}', $format);
    }
    $Date = date($format, $timestamp);
    if ($HasMonth === true) {
        $Month = prettyMonth(date('m', $timestamp), $variant);
        $Date = str_replace('{|_|}', $Month, $Date);
    }

    return $Date;
}

function ShowBuildTime($time)
{
    global $_Lang;

    return "<br/>{$_Lang['ConstructionTime']}: ".pretty_time($time);
}

function Array2String($Array)
{
    foreach($Array as $Key => $Value)
    {
        $String[] = "{$Key},{$Value}";
    }
    return implode(';', $String);
}

function String2Array($String)
{
    $String = explode(';', $String);
    foreach($String as $Data)
    {
        if(empty($Data))
        {
            break;
        }
        $Data = explode(',', $Data);
        $Array[$Data[0]] = $Data[1];
    }
    return (isset($Array) ? $Array : null);
}

?>
