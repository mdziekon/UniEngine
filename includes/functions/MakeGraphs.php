<?php

/**
 *
 * MakeGraphs.php
 *
 * @version 1.0.3.1
 * @copyright 2012 by mdziekon for UniEngine [https://github.com/mdziekon/UniEngine]
 * @using awfy [http://hg.mozilla.org/users/danderson_mozilla.com/awfy] (adjusted by mdziekon)
 */

function MakeGraphs($Modes, $Scores, $Dimensions = array(), $OwnTooltipCode = false)
{
    global $_EnginePath;
    static $FirstRun = true;

    if($FirstRun === true)
    {
        $FirstRun = false;
    }
    else
    {
        return false;
    }

    if(empty($Modes) OR empty($Scores))
    {
        return false;
    }
    foreach($Scores as $Index => $Data)
    {
        if(empty($Data))
        {
            unset($Scores[$Index]);
        }
    }
    if(empty($Scores))
    {
        return false;
    }

    require($_EnginePath.'vendor/smarty/smarty/libs/Smarty.class.php');

    $smarty = new Smarty();

    $smarty
        ->addTemplateDir($_EnginePath . UNIENGINE_TEMPLATE_DIR . UNIENGINE_TEMPLATE_NAME . '/')
        ->setCompileDir($_EnginePath . '/tmp/smarty/compiled/')
        ->setCacheDir($_EnginePath . '/tmp/smarty/cached/');

    $graphs = array();
    $cx = new Context($Modes);

    foreach($Scores as $Index => $Data)
    {
        foreach($Data as $SubIndex => $Data2)
        {
            if($SubIndex === 'data')
            {
                $GraphData[$Index] = $Data2;
                unset($Scores[$Index][$SubIndex]);
                break;
            }
        }
    }

    $SingleGraphTPL = gettemplate('Graphs_SingleGraph');
    foreach($Scores as $Index => $Data)
    {
        $GraphName = 'Graph_'.(string)($Index + 1);

        $filter = RunFilter::FromGET($cx, $Data);
        $gb = new GraphBuilder($cx, $filter);

        $graphs[$GraphName] = array('name' => $GraphName, 'graph' => $gb, 'graphdata' => $GraphData[$Index]);

        $ReturnGraphs[] = parsetemplate($SingleGraphTPL, array('GraphName' => $GraphName));
    }

    if($Dimensions['x'] <= 0)
    {
        $Dimensions['x'] = 500;
    }
    if($Dimensions['y'] <= 0)
    {
        $Dimensions['y'] = 300;
    }
    $smarty->assign('RootPaht', $_EnginePath);
    $smarty->assign('cx', $cx);
    $smarty->assign('graphs', $graphs);
    $smarty->assign('width', $Dimensions['x']);
    $smarty->assign('height', $Dimensions['y']);
    if($OwnTooltipCode)
    {
        $smarty->assign('OwnTooltipCode', $OwnTooltipCode);
    }

    return array('includes' => $smarty->fetch('Graphs_Body.tpl'), 'graphs' => $ReturnGraphs, 'legend' => $smarty->fetch('Graphs_Legend.tpl'));
}

class Context
{
    var $vendors = [];
    var $modes = [];

    function __construct($Modes)
    {
        $Vendors = array(array('id' => 1, 'name' => '', 'vendor' => '', 'browser' => ''));
        foreach($Vendors as $Index => $Data)
        {
            $this->vendorMap_[$Data['id']] = count($this->vendors);
            $this->vendors[] = $Data;
        }

        foreach($Modes as $Index => $Data)
        {
            $this->modeMap_[$Data['id']] = count($this->modes);
            $Data['vendor'] = $this->vendorMap_[$Data['vendor_id']];
            $Data['used'] = false;
            $this->modes[] = $Data;
        }
    }

    function UpdateContext($Modes)
    {
        foreach($Modes as $Index => $Data)
        {
            $this->modeMap_[$Data['id']] = count($this->modes);
            $Data['vendor'] = $this->vendorMap_[$Data['vendor_id']];
            $Data['used'] = false;
            $this->modes[] = $Data;
        }
    }

    function markModeUsed($mode_id)
    {
        $mode = $this->modeFromDB($mode_id);
        $this->modes[$mode]['used'] = true;
    }

    function modeFromDB($mode_id)
    {
        return $this->modeMap_[$mode_id];
    }
}

class RunFilter
{
    var $AllScores;
    var $run_points;
    var $runs;
    var $runmap = [];
    var $modemap = [];
    var $series = [];

    function __construct($cx, $AllScores)
    {
        $this->AllScores = $AllScores;

        $this->runs = $this->findRuns();

        // Get the list of run IDs for this graph.
        $HasRuns = false;
        $RunMapID = 0;
        foreach($this->runs as $Index => $Data)
        {
            $HasRuns = true;
            $Data['stamp'] = substr($Data['stamp'], 0, 30);
            if(empty($StampsIDs[$Data['stamp']]) && (!isset($StampsIDs[$Data['stamp']]) || $StampsIDs[$Data['stamp']] !== 0))
            {
                $StampsIDs[$Data['stamp']] = $RunMapID;
                $RunMapID += 1;
            }
            $this->runmap[$Data['id']] = $StampsIDs[$Data['stamp']];
        }
        $this->run_points = array_flip($StampsIDs);
        foreach($this->run_points as &$ThisVal)
        {
            $ThisVal = substr($ThisVal, 0, 10);
        }
        $ModesInTable = array();

        foreach($this->AllScores as $Data)
        {
            $mode_id = $Data['mode_id'];
            $ThisMode = $cx->modeFromDB($mode_id);
            if(!in_array($ThisMode, $ModesInTable))
            {
                $this->modemap[$mode_id] = count($this->series);
                $this->series[] = array('mode' => $cx->modeFromDB($mode_id));
                $cx->markModeUsed($mode_id);
                $ModesInTable[] = $ThisMode;
            }
        }
    }

    function findSeriesOfMode($mode_id)
    {
        return $this->modemap[$mode_id];
    }

    function findRuns()
    {
        $TempScores = $this->AllScores;
        if(empty($TempScores))
        {
            return array();
        }
        foreach($TempScores as $Index => $Data)
        {
            $TempStamps[$Index] = $Data['stamp'];
        }
        array_multisort($TempScores, SORT_DESC, $TempStamps);

        foreach($TempScores as $Data)
        {
            $rows[] = array('id' => $Data['run_id'], 'stamp' => $Data['stamp']);
        }
        return array_reverse($rows);
    }

    function findRun($run_id)
    {
        return $this->runmap[$run_id];
    }

    static function FromGET($cx, $AllScores)
    {
        return new RunFilter($cx, $AllScores);
    }
}

class GraphBuilder
{
    var $AllScores;
    var $series;
    var $runs;
    var $run_points;

    function __construct(&$cx, &$filter)
    {
        $this->AllScores = $filter->AllScores;
        $this->runs = $filter->runs;
        $this->run_points = $filter->run_points;
        $this->series = $filter->series;

        foreach($this->AllScores as $Index => $Data)
        {
            $run_id = $Data['run_id'];
            $mode_id = $Data['mode_id'];
            $mode = $filter->findSeriesOfMode($mode_id);
            $run = $filter->findRun($run_id);
            $this->series[$mode]['scores'][$run] = $Data['score'];
        }
    }
}

?>
