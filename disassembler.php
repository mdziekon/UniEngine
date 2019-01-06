<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

loggedCheck();

if(!isPro())
{
    message($_Lang['ThisPageOnlyForPro'], $_Lang['ProAccount']);
}

$ElementsPerRow = 7;
$CurrentPlanet = &$_Planet;
$ElementPriceArray = null;

includeLang('buildings');
$Parse = &$_Lang;
$Parse['SkinPath'] = $_SkinPath;

PlanetResourceUpdate($_User, $CurrentPlanet, time());

// Parse Command & Disassembly
if(isset($_POST['cmd']) && $_POST['cmd'] == 'exec')
{
    $SomethinkAdded = false;
    if(!empty($_POST['elem']))
    {
        foreach($_POST['elem'] as $ID => $Count)
        {
            $ID = intval($ID);
            $Count = floor(floatval(str_replace('.', '', $Count)));
            if($Count > 0)
            {
                $SomethinkAdded = true;
                if(in_array($ID, $_Vars_ElementCategories['fleet']) OR in_array($ID, $_Vars_ElementCategories['defense']))
                {
                    if($CurrentPlanet[$_Vars_GameElements[$ID]] > 0)
                    {
                        if($CurrentPlanet[$_Vars_GameElements[$ID]] < $Count)
                        {
                            $Count = $CurrentPlanet[$_Vars_GameElements[$ID]];
                        }
                        $Disassemble[$ID] = $Count;
                    }
                }
            }
        }
    }

    if($SomethinkAdded)
    {
        if(!empty($Disassemble))
        {
            $AddMet = 0;
            $AddCry = 0;
            $AddDeu = 0;
            foreach($Disassemble as $ID => $Count)
            {
                $AddMet += $_Vars_Prices[$ID]['metal'] * $Count * DISASSEMBLER_PERCENT;
                $AddCry += $_Vars_Prices[$ID]['crystal'] * $Count * DISASSEMBLER_PERCENT;
                $AddDeu += $_Vars_Prices[$ID]['deuterium'] * $Count * DISASSEMBLER_PERCENT;
                $CurrentPlanet[$_Vars_GameElements[$ID]] -= $Count;
                $PlanetUpdate[] = "`{$_Vars_GameElements[$ID]}` = `{$_Vars_GameElements[$ID]}` - {$Count}";

                $DevLog_Array[] = "{$ID},{$Count}";
            }

            $CurrentPlanet['metal'] += $AddMet;
            $CurrentPlanet['crystal'] += $AddCry;
            $CurrentPlanet['deuterium'] += $AddDeu;
            $QryPlanetUpdate = "UPDATE {{table}} SET ";
            $QryPlanetUpdate .= implode(',', $PlanetUpdate);
            $QryPlanetUpdate .= " WHERE `id` = {$CurrentPlanet['id']};";
            doquery($QryPlanetUpdate, 'planets');
            $UserDev_Log[] = array('PlanetID' => $CurrentPlanet['id'], 'Date' => time(), 'Place' => 24, 'Code' => '0', 'ElementID' => '0', 'AdditionalData' => 'P,'.(DISASSEMBLER_PERCENT * 100).';'.implode(';', $DevLog_Array));

            $Parse['Create_DisassembleResult'] = sprintf($_Lang['Disassembler_Sold_units'], prettyNumber($AddMet), prettyNumber($AddCry), prettyNumber($AddDeu));
            $Parse['Create_DisassembleResult_Color'] = 'lime';
        }
        else
        {
            $Parse['Create_DisassembleResult'] = sprintf($_Lang['Disassembler_NoUnits_on_that_planet'], (($CurrentPlanet['planet_type'] == 1) ? $_Lang['on_this_planet'] : $_Lang['on_this_moon']));
            $Parse['Create_DisassembleResult_Color'] = 'red';
        }
    }
    else
    {
        $Parse['Create_DisassembleResult_Color'] = 'orange';
        $Parse['Create_DisassembleResult'] = $_Lang['Disassembler_Nothink_in_post'];
    }
}

if(empty($Parse['Create_DisassembleResult']))
{
    $Parse['HideDisassembleResult'] = 'hide';
}

// Generate ElementsList
$TPL['list_element'] = gettemplate('buildings_compact_list_element_shipyard');
$TPL['list_hidden'] = gettemplate('buildings_compact_list_hidden');
$TPL['list_row'] = gettemplate('buildings_compact_list_row');
$TPL['list_breakrow'] = gettemplate('buildings_compact_list_breakrow');
$TPL['list_disabled'] = gettemplate('buildings_compact_list_disabled');
$TPL['list_disabled'] = parsetemplate($TPL['list_disabled'], array('AddOpacity' => ''));

$ElementParserDefault = array
(
    'SkinPath' => $_SkinPath,
    'InfoBox_Count' => $_Lang['InfoBox_Count'],
    'InfoBox_Build' => $_Lang['InfoBox_DoResearch'],
);

$TabIndex = 1;

$CombineReslist = array_merge($_Vars_ElementCategories['fleet'], $_Vars_ElementCategories['defense']);
foreach($CombineReslist as $ElementID)
{
    if(in_array($ElementID, $_Vars_ElementCategories['fleet']))
    {
        $Type = 'fleet';
    }
    else
    {
        $Type = 'defense';
    }
    $ElementParser = $ElementParserDefault;

    $BlockZeroCount = ($CurrentPlanet[$_Vars_GameElements[$ElementID]] > 0 ? false : true);

    $ElementParser['ElementCount'] = prettyNumber($CurrentPlanet[$_Vars_GameElements[$ElementID]]);
    if(strlen($ElementParser['ElementCount']) > 10)
    {
        $ElementParser['IsBigNum'] = 'bignum';
    }
    $ElementParser['ElementName'] = $_Lang['tech'][$ElementID];
    $ElementParser['ElementID'] = $ElementID;

    if(!$BlockZeroCount)
    {
        $ElementParser['ElementPrice'] = GetBuildingPrice($_User, $CurrentPlanet, $ElementID, true, false, true);
        foreach($ElementParser['ElementPrice'] as $Key => $Value)
        {
            if($Value > 0)
            {
                $ElementPriceArray[$ElementID][$Key] = $Value * DISASSEMBLER_PERCENT;
            }
        }
    }

    $BlockReason = array();
    if($BlockZeroCount)
    {
        $BlockReason[] = $_Lang['ListBox_Disallow_NoElements'];
    }
    if(!empty($BlockReason))
    {
        $ElementParser['ElementDisabled'] = $TPL['list_disabled'];
        $ElementParser['ElementDisableInv'] = 'inv';
        $ElementParser['ElementDisableReason'] = end($BlockReason);
    }
    else
    {
        $ElementParser['TabIndex'] = $TabIndex;
        $TabIndex += 1;
    }

    $StructuresList[$Type][] = parsetemplate($TPL['list_element'], $ElementParser);
}

// Create List
foreach($StructuresList as $Type => $Rows)
{
    $ThisRowIndex = 0;
    $InRowCount = 0;
    foreach($Rows as $ParsedData)
    {
        if($InRowCount == $ElementsPerRow)
        {
            $ParsedRows[$Type][($ThisRowIndex + 1)] = $TPL['list_breakrow'];
            $ThisRowIndex += 2;
            $InRowCount = 0;
        }

        if(!isset($StructureRows[$Type][$ThisRowIndex]['Elements']))
        {
            $StructureRows[$Type][$ThisRowIndex]['Elements'] = '';
        }
        $StructureRows[$Type][$ThisRowIndex]['Elements'] .= $ParsedData;
        $InRowCount += 1;
    }
    if($InRowCount < $ElementsPerRow)
    {
        $StructureRows[$Type][$ThisRowIndex]['Elements'] .= str_repeat($TPL['list_hidden'], ($ElementsPerRow - $InRowCount));
    }
    foreach($StructureRows[$Type] as $Index => $Data)
    {
        $ParsedRows[$Type][$Index] = parsetemplate($TPL['list_row'], $Data);
    }
    ksort($ParsedRows[$Type], SORT_ASC);
}

$Parse['Create_StructuresList_Ships'] = implode('', $ParsedRows['fleet']);
$Parse['Create_StructuresList_Defense'] = implode('', $ParsedRows['defense']);
$Parse['Create_InsertPrices'] = json_encode($ElementPriceArray);
$Parse['Create_DisassemblerPercent'] = sprintf($_Lang['Disassembler_ReturnTip'], (DISASSEMBLER_PERCENT * 100));
if($_User['settings_useprettyinputbox'] == 1)
{
    $Parse['P_AllowPrettyInputBox'] = 'true';
}
else
{
    $Parse['P_AllowPrettyInputBox'] = 'false';
}
// End of - Parse all available ships

$page = parsetemplate(gettemplate('disassembler_body'), $Parse);

display($page, $_Lang['Disassembler_Title']);

?>
