<?php

function GetTechnoPoints($CurrentUser)
{
    global $_Vars_GameElements, $_Vars_Prices, $_Vars_ElementCategories;
    static $ResourcesCost, $Cached, $CachedFactors;

    $TechCounts = 0;
    $TechPoints = 0;
    $TechArr = null;
    foreach($_Vars_ElementCategories['tech'] as $Techno)
    {
        $ThisLevel = $CurrentUser[$_Vars_GameElements[$Techno]];
        if($ThisLevel > 0)
        {
            $FactorSum = 0;
            $TechCounts += $ThisLevel;
            $TechArr[$Techno] = $ThisLevel;
            $CacheEmpty = false;

            if(isset($Cached[$Techno][$ThisLevel]))
            {
                // This Technology was already calculated on that level - get points from Cache
                $TechPoints += $Cached[$Techno][$ThisLevel];
                continue;
            }
            else if(isset($Cached[$Techno]) && count($Cached[$Techno]) > 1)
            {
                // This Technology was calculated, but on lower level - get points from that level
                $StartingLevel = count($Cached[$Techno]);
            }
            else
            {
                // This Technology was never seen before
                $StartingLevel = 1;
                $CacheEmpty = true;
            }

            if(isset($ResourcesCost[$Techno]))
            {
                $Units = $ResourcesCost[$Techno];
            }
            else
            {
                $Units = $_Vars_Prices[$Techno]['metal'] + $_Vars_Prices[$Techno]['crystal'] + $_Vars_Prices[$Techno]['deuterium'];
                $ResourcesCost[$Techno] = $Units;
            }
            if($CacheEmpty)
            {
                $Cached[$Techno][1] = $Units;
            }
            else
            {
                $FactorSum += $CachedFactors[$Techno][$StartingLevel];
            }

            for($Level = $StartingLevel; $Level < $ThisLevel; $Level += 1)
            {
                $FactorSum += pow($_Vars_Prices[$Techno]['factor'], $Level);
                $CachedFactors[$Techno][($Level + 1)] = $FactorSum;
                $Cached[$Techno][($Level + 1)] = $Units * ($FactorSum + 1);
            }
            $TechPoints += $Cached[$Techno][$ThisLevel];
        }
    }
    $RetValue['TechCount'] = $TechCounts;
    $RetValue['TechPoint'] = $TechPoints;
    $RetValue['TechArr'] = $TechArr;

    return $RetValue;
}

function GetBuildPoints($CurrentPlanet)
{
    global $_Vars_GameElements, $_Vars_Prices, $_Vars_ElementCategories;
    static $ResourcesCost, $Cached, $CachedFactors;

    $BuildCounts = 0;
    $BuildPoints = 0;
    $BuildArr = null;
    foreach($_Vars_ElementCategories['build'] AS $Building)
    {
        $ThisLevel = $CurrentPlanet[$_Vars_GameElements[$Building]];
        if($ThisLevel > 0)
        {
            $FactorSum = 0;
            $BuildCounts += $ThisLevel;
            $BuildArr[$Building] = $ThisLevel;
            $CacheEmpty = false;

            if(isset($Cached[$Building][$ThisLevel]))
            {
                // This Building was already calculated on that level - get points from Cache
                $BuildPoints += $Cached[$Building][$ThisLevel];
                continue;
            }
            else if(isset($Cached[$Building]) && count($Cached[$Building]) > 1)
            {
                // This Building was calculated, but on lower level - get points from that level
                $StartingLevel = count($Cached[$Building]);
            }
            else
            {
                // This Building was never seen before
                $StartingLevel = 1;
                $CacheEmpty = true;
            }

            if(isset($ResourcesCost[$Building]))
            {
                $Units = $ResourcesCost[$Building];
            }
            else
            {
                $Units = $_Vars_Prices[$Building]['metal'] + $_Vars_Prices[$Building]['crystal'] + $_Vars_Prices[$Building]['deuterium'];
                $ResourcesCost[$Building] = $Units;
            }
            if($CacheEmpty)
            {
                $Cached[$Building][1] = $Units;
            }
            else
            {
                $FactorSum += $CachedFactors[$Building][$StartingLevel];
            }

            for($Level = $StartingLevel; $Level < $ThisLevel; $Level += 1)
            {
                $FactorSum += pow($_Vars_Prices[$Building]['factor'], $Level);
                $CachedFactors[$Building][($Level + 1)] = $FactorSum;
                $Cached[$Building][($Level + 1)] = $Units * ($FactorSum + 1);
            }
            $BuildPoints += $Cached[$Building][$ThisLevel];
        }
    }
    $RetValue['BuildCount'] = $BuildCounts;
    $RetValue['BuildPoint'] = $BuildPoints;
    $RetValue['BuildArr'] = $BuildArr;

    return $RetValue;
}

function GetDefensePoints($CurrentPlanet)
{
    global $_Vars_GameElements, $_Vars_Prices, $_Vars_ElementCategories;

    $DefenseCounts = 0;
    $DefensePoints = 0;
    $DefenseArr = null;
    foreach($_Vars_ElementCategories['defense'] as $Defense)
    {
        if($CurrentPlanet[$_Vars_GameElements[$Defense]] > 0)
        {
            $Units = $_Vars_Prices[$Defense]['metal'] + $_Vars_Prices[$Defense]['crystal'] + $_Vars_Prices[$Defense]['deuterium'];
            $DefensePoints += ($Units * $CurrentPlanet[$_Vars_GameElements[$Defense]]);
            $DefenseCounts += $CurrentPlanet[$_Vars_GameElements[$Defense]];
            $DefenseArr[$Defense] = $CurrentPlanet[$_Vars_GameElements[$Defense]];
        }
    }
    $RetValue['DefenseCount'] = $DefenseCounts;
    $RetValue['DefensePoint'] = $DefensePoints;
    $RetValue['DefenseArr'] = $DefenseArr;

    return $RetValue;
}

function GetFleetPoints($CurrentPlanet)
{
    global $_Vars_GameElements, $_Vars_Prices, $_Vars_ElementCategories;

    $FleetCounts = 0;
    $FleetPoints = 0;
    $FleetArr = null;
    foreach($_Vars_ElementCategories['fleet'] as $Fleet)
    {
        if($CurrentPlanet[$_Vars_GameElements[$Fleet]] > 0)
        {
            $Units = $_Vars_Prices[$Fleet]['metal'] + $_Vars_Prices[$Fleet]['crystal'] + $_Vars_Prices[$Fleet]['deuterium'];
            $FleetPoints += ($Units * $CurrentPlanet[$_Vars_GameElements[$Fleet]]);
            $FleetCounts += $CurrentPlanet[$_Vars_GameElements[$Fleet]];
            $FleetArr[$Fleet] = $CurrentPlanet[$_Vars_GameElements[$Fleet]];
        }
    }
    $RetValue['FleetCount'] = $FleetCounts;
    $RetValue['FleetPoint'] = $FleetPoints;
    $RetValue['FleetArr'] = $FleetArr;

    return $RetValue;
}

function GetFleetPointsOnTour($CurrentFleet)
{
    global $_Vars_Prices;

    $FleetCounts = 0;
    $FleetPoints = 0;
    $FleetArr = null;

    $Fleet = explode(';', $CurrentFleet);
    foreach($Fleet as $Data)
    {
        if(!empty($Data))
        {
            $Data = explode(',', $Data);
            $Price = $_Vars_Prices[$Data[0]]['metal'] + $_Vars_Prices[$Data[0]]['crystal'] + $_Vars_Prices[$Data[0]]['deuterium'];
            $FleetPoints += $Price * $Data[1];
            $FleetCounts += $Data[1];
            $FleetArr[$Data[0]] = $Data[1];
        }
    }

    $RetValue['FleetCount'] = $FleetCounts;
    $RetValue['FleetPoint'] = $FleetPoints;
    $RetValue['FleetArr'] = $FleetArr;

    return $RetValue;
}

?>
