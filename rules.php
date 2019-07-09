<?php

define('INSIDE', true);
define('IN_RULES', true);

$_DontShowMenus = true;
$_DontCheckPolls = true;
$_AllowInVacationMode = true;

$_EnginePath = './';
include($_EnginePath.'common.php');

includeLang('rules');
$TPL = gettemplate('rules_body');

if (
    isLogged() &&
    isRulesAcceptanceRequired($_User, $_GameConfig)
) {
    $IsInDelete = ($_User['is_ondeletion'] == 1 ? true : false);

    if(isset($_GET['cmd']))
    {
        if($_GET['cmd'] == 'decline')
        {
            if($IsInDelete !== true)
            {
                $_User['deletion_endtime'] = time() + (ACCOUNT_DELETION_TIME * TIME_DAY);
                doquery("UPDATE {{table}} SET `is_ondeletion` = 1, `deletion_endtime` = {$_User['deletion_endtime']} WHERE `id` = {$_User['id']} LIMIT 1;", 'users');
                $IsInDelete = true;
                $_User['is_ondeletion'] = 1;
            }
        }
        else if($_GET['cmd'] == 'accept')
        {
            $AddToUpdate = '';
            if($IsInDelete === true)
            {
                $AddToUpdate = ", `is_ondeletion` = 0, `deletion_endtime` = 0";
            }
            doquery("UPDATE {{table}} SET `rules_accept_stamp` = {$_GameConfig['last_rules_changes']} {$AddToUpdate} WHERE `id` = {$_User['id']} LIMIT 1;", 'users');
            header('Location: overview.php');
            safeDie();
        }
    }

    if($_User['is_ondeletion'] == 1)
    {
        $_Lang['AcceptBox_Option_Accept'] = $_Lang['AcceptBox_Option_Accept1'];
        $_Lang['AcceptBox_Option_Decline'] = $_Lang['AcceptBox_Option_Decline1'];
        $_Lang['AcceptBox_InsertDeleteTime'] = sprintf($_Lang['AcceptBox_DeleteTime'], prettyDate('d m Y \o H:i:s', intval($_User['deletion_endtime']), 1));
    }
    else
    {
        $_Lang['AcceptBox_Option_Accept'] = $_Lang['AcceptBox_Option_Accept0'];
        $_Lang['AcceptBox_Option_Decline'] = $_Lang['AcceptBox_Option_Decline0'];
    }
    $_Lang['AcceptBox_Info'] = sprintf($_Lang['AcceptBox_Info'], prettyDate('d m Y, H:i:s', $_GameConfig['last_rules_changes'], 1));

    $TPL = str_replace('{InsertRulesAcceptanceBox}', gettemplate('rules_acceptbox'), $TPL);
}

function createRulesIndex (&$rulesElements, $prefix = []) {
    $templates = [
        'list_indexlist_set' => gettemplate('rules_list_indexlist_set'),
        'list_indexlist_element' => gettemplate('rules_list_indexlist_element'),
        'list_indexlist_subrules' => gettemplate('rules_list_indexlist_subrules')
    ];

    $result = [];

    foreach ($rulesElements as $idx => &$element) {
        if (!is_array($element)) {
            continue;
        }
        if (!isset($element['title'])) {
            continue;
        }

        $currentIdx = array_merge($prefix, [ $idx ]);

        $subrules = (
            isset($element['elements']) ?
            createRulesIndex($element['elements'], $currentIdx) :
            null
        );

        $tplVariables = [
            'RuleLink_Idx' => implode('_', $currentIdx),
            'RuleLabel_Idx' => implode('.', $currentIdx) . '.',
            'RuleLabel_Title' => $element['title'],
            'Subrules' => (
                $subrules !== null ?
                parsetemplate($templates['list_indexlist_subrules'], [ 'Subrules' => $subrules ]) :
                ''
            )
        ];

        $result[] = parsetemplate($templates['list_indexlist_element'], $tplVariables);
    }

    return implode('', $result);
}

function createRulesList (&$rulesElements, $prefix = [], $hasNoHeader = false) {
    $templates = [
        'list_content_header_group' => gettemplate('rules_list_content_header_group'),
        'list_content_header_subgroup' => gettemplate('rules_list_content_header_subgroup'),
        'list_content_rules_ruleitem' => gettemplate('rules_list_content_rules_ruleitem'),
        'list_content_rules_rulesubitem' => gettemplate('rules_list_content_rules_rulesubitem'),
    ];

    $result = [];

    foreach ($rulesElements as $idx => &$element) {
        $currentIdx = array_merge($prefix, [ $idx ]);

        if (!is_array($element)) {
            // Is plain old string, simple rule
            $tplVariables = [
                'RuleLabel_Idx' => implode('.', $currentIdx) . '.',
                'RuleContent' => $element,
                'RuleSubcontent_IsHiddenStyle' => 'display: none;',
                'RuleSubcontent' => ''
            ];

            $result[] = parsetemplate(
                $templates['list_content_rules_ruleitem'],
                $tplVariables
            );

            continue;
        }
        if (isset($element['maintext'])) {
            // Is complex rule
            $subcontent = [];

            foreach($element['ul'] as &$subelement) {
                $subelementTplVariables = [
                    'RuleSubitem' => $subelement
                ];

                $subcontent[] = parsetemplate(
                    $templates['list_content_rules_rulesubitem'],
                    $subelementTplVariables
                );
            }

            $tplVariables = [
                'RuleLabel_Idx' => implode('.', $currentIdx) . '.',
                'RuleContent' => $element['maintext'],
                'RuleSubcontent_IsHiddenClass' => 'hidden',
                'RuleSubcontent' => implode('', $subcontent)
            ];

            $result[] = parsetemplate(
                $templates['list_content_rules_ruleitem'],
                $tplVariables
            );

            continue;
        }

        if (!isset($element['title'])) {
            // Is simple grouping (no header)
            $result[] = createRulesList($element['elements'], $currentIdx, true);

            continue;
        }

        // Is rules group
        $rulesContent = createRulesList($element['elements'], $currentIdx);
        $isTopLevel = (count($currentIdx) === 1);
        $hasOwnRules = (
            isset($element['elements']) &&
            isset($element['elements'][1]) &&
            !isset($element['elements'][1]['elements'])
        );

        $tplBody = (
            $isTopLevel ?
            $templates['list_content_header_group'] :
            $templates['list_content_header_subgroup']
        );

        $tplVariables = [
            'RuleLink_Idx' => implode('_', $currentIdx),
            'RuleLabel_Idx' => implode('.', $currentIdx) . '.',
            'RuleLabel_Title' => $element['title'],
            'RulesContent' => $rulesContent,
            'RulesContent_IsHiddenStyle' => (
                !$hasOwnRules ?
                "display: none;" :
                ""
            )
        ];

        $result[] = parsetemplate($tplBody, $tplVariables);
    }

    return implode('', $result);
}

if (langFileExists("rules.definitions.custom")) {
    includeLang("rules.definitions.custom");
} else {
    includeLang("rules.definitions.default");
}

$_Lang['ParsedRules_IndexList'] = createRulesIndex($_Lang['RulesDefinitions'], []);
$_Lang['ParsedRules_ContentList'] = createRulesList($_Lang['RulesDefinitions'], []);

display(parsetemplate($TPL, $_Lang), $_Lang['Page_Title'], false);

?>
