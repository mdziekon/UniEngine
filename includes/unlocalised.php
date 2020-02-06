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

function createLocalTemplateLoader($loaderDirPath) {
    return function ($templateName) use ($loaderDirPath) {
        return ReadFromFile($loaderDirPath . '/' . $templateName . '.tpl');
    };
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

function Array2String($elements) {
    $packedElements = [];

    foreach ($elements as $elementKey => $elementValue) {
        $packedElements[] = "{$elementKey},{$elementValue}";
    }

    return implode(';', $packedElements);
}

function String2Array($content) {
    $result = [];

    $contentElements = explode(';', $content);

    foreach ($contentElements as $element) {
        if (empty($element)) {
            break;
        }

        $element = explode(',', $element);

        $elementKey = $element[0];
        $elementValue = $element[1];

        $result[$elementKey] = $elementValue;
    }

    return (
        !empty($result) ?
        $result :
        null
    );
}

?>
