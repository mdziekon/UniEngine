<?php

/**
 * phpBench
 *
 * @author      mdziekon
 * @copyright   mdziekon 2010 - 2011 [All rights reserved]
 * @version     1.6.2.2
 */

class phpBench
{
    private $TimeArray = array();
    private $MemArray = array();
    private $SimpleCountArray = array();
    private $StartTime;
    private $InitMem;
    private $CountersStarted = 0;

    private $ShowSimpleCount = true;
    private $echoTable = true;

    private $Result = false;

    private function humanMemSize($Mem)
    {
        if($Mem > 1048576)
        {
            $Unit = 1048576;
            $UnitName = 'MB';
        }
        else if($Mem > 1024)
        {
            $Unit = 1024;
            $UnitName = 'KB';
        }
        else
        {
            $Unit = 1;
            $UnitName = 'B';
        }
        return sprintf('%0.4f', ($Mem/$Unit)).' '.$UnitName;
    }

    public function breakPoint()
    {
        $this->TimeArray[] = $this->getTime();
        $this->MemArray[] = $this->humanMemSize(memory_get_usage());
    }

    public function newStart()
    {
        $this->StartTime = microtime(true);
    }

    public function simpleCountStart($ReturnKey = false, $SetName = '')
    {
        $this->SimpleCountArray[] = array('start' => microtime(true), 'startram' => $this->humanMemSize(memory_get_usage()), 'name' => $SetName);
        $this->CountersStarted += 1;
        if($ReturnKey)
        {
            $GetKeys = array_keys($this->SimpleCountArray);
            return $GetKeys[(count($GetKeys) - 1)];
        }
    }

    public function simpleCountStop($ReturnKey = false)
    {
        if($this->CountersStarted > 0)
        {
            $Temp = $this->SimpleCountArray;
            krsort($Temp);
            foreach($Temp as $Key => $Value)
            {
                if(empty($Value['result']))
                {
                    $this->SimpleCountArray[$Key]['result'] = microtime(true) - $this->SimpleCountArray[$Key]['start'];
                    $this->SimpleCountArray[$Key]['endram'] = $this->humanMemSize(memory_get_usage());
                    $this->SimpleCountArray[$Key]['assocLevel'] = $this->CountersStarted;
                    if($this->CountersStarted > 1)
                    {
                        $this->SimpleCountArray[$Key]['assoc'] = true;
                    }
                    if($ReturnKey)
                    {
                        $StopedKey = $Key;
                    }
                    break;
                }
            }
            $this->CountersStarted -= 1;
            if($ReturnKey)
            {
                return $StopedKey;
            }
        }
        return false;
    }

    public function showSimpleCountSwitch()
    {
        if($this->ShowSimpleCount)
        {
            $this->ShowSimpleCount = false;
        }
        else
        {
            $this->ShowSimpleCount = true;
        }
    }

    public function echoTableSwitch()
    {
        if($this->echoTable)
        {
            $this->echoTable = false;
        }
        else
        {
            $this->echoTable = true;
        }
    }

    private function getTime()
    {
        return (microtime(true) - $this->StartTime);
    }

    function __construct()
    {
        $this->InitMem = $this->humanMemSize(memory_get_usage());
        $this->StartTime = microtime(true);
    }

    private function prepareResults()
    {
        if($this->echoTable)
        {
            $this->breakPoint();
            $endKey = count($this->TimeArray) - 1;

            $Echo = '<br/><br/><table width="850"><tbody><tr><th class="c" colspan="5"><b>Wyniki pomiarów</b></th></tr><tr><th class="c" style="width: 20%;"><b>Nazwa pkt.</b></th><th class="c" style="width: 5%;"><b>Nr.</b></th><th class="c" style="width: 5%;"><b>Poziom</b></th><th class="c" style="width: 40%;"><b>Czas wykonywania</b></th><th class="c" style="width: 30%;"><b>Zużycie pamięci</b></th></tr>';

            $Echo .= '<tr><th class="c"><b>Init Mem</b></th><th class="c"><b>#0</b><th class="c">&nbsp;</th><th class="c">&nbsp;</th><th class="c"><b>'.$this->InitMem.'</b></th></tr>';

            $TotalTime = 0;
            foreach($this->TimeArray as $key => $val)
            {
                $TotalTime += $val;
                $Echo .= '<tr><th class="c">';
                if($key != $endKey)
                {
                    $Echo .= 'BreakPoint</th><th class="c"><b>#'.($key+1).'</b>';
                }
                else
                {
                    $Echo .= 'EndPoint</th><th class="c"><b>#'.($endKey+1).'</b>';
                }
                $Echo .= '</th><th class="c">&nbsp;<th class="c">'.sprintf('%0.20f', $val).' s.</th><th class="c">'.$this->MemArray[$key].'</th></tr>';
            }

            if(!empty($this->SimpleCountArray[0]['result']) AND $this->ShowSimpleCount)
            {
                $Echo .= '<tr><th class="c" colspan="5"><b>Proste Pomiary Użytkownika</b></th></tr>';
                foreach($this->SimpleCountArray as $Index => $Data)
                {
                    if(!empty($Data['result']))
                    {
                        if(empty($PreviousParentID))
                        {
                            $PreviousParentID[] = $Index;
                            $PreviousAssocLevel = 1;
                        }
                        $Difference = $PreviousAssocLevel - $Data['assocLevel'];
                        if($Difference == -2)
                        {
                            $PreviousParentID[] = $PreviousKey;
                            $PreviousAssocLevel = $this->SimpleCountArray[end($PreviousParentID)]['assocLevel'];
                        }
                        else if($Difference >= 0)
                        {
                            for($i = 0; $i <= $Difference; $i += 1)
                            {
                                array_pop($PreviousParentID);
                            }
                            if(empty($PreviousParentID))
                            {
                                $PreviousParentID[] = $Index;
                                $PreviousAssocLevel = 1;
                            }
                            else
                            {
                                $PreviousAssocLevel = $this->SimpleCountArray[end($PreviousParentID)]['assocLevel'];
                            }
                        }
                        $PreviousKey = $Index;

                        if(isset($Data['assoc']) && $Data['assoc'] == true)
                        {
                            $ThisTime = $this->SimpleCountArray[end($PreviousParentID)]['result'];
                        }
                        else
                        {
                            $ThisTime = $TotalTime;
                        }
                        $Echo .= '<tr><th class="c '.(isset($Data['assoc']) && $Data['assoc'] == true ? 'orange' : '').'">Pomiar'.(!empty($Data['name']) ? '<br/>['.$Data['name'].']' : '').'</th><th class="c '.(isset($Data['assoc']) && $Data['assoc'] == true ? 'orange' : '').'"><b>#'.($Index+1).'</b></th><th class="c '.(isset($Data['assoc']) && $Data['assoc'] == true ? 'orange' : '').'"><b>&#187; '.$Data['assocLevel'].'</b></th>';
                        $Echo .= '<th class="c">'.sprintf('%0.20f', $Data['result']).' s.<br/>('.sprintf('%0.8f', ($Data['result']/$ThisTime) * 100).'%)</th><th class="c">'.$Data['startram'].' / '.$Data['endram'].'</th></tr>';
                    }
                }
            }

            $Echo .= '</tbody></table>';

            $this->Result = $Echo;
        }
    }

    public function ReturnResult()
    {
        if($this->Result === FALSE)
        {
            $this->prepareResults();
        }
        return $this->Result;
    }

    public function ReturnTimeArray()
    {
        return $this->TimeArray;
    }

    public function ReturnSimpleCountArray()
    {
        return $this->SimpleCountArray;
    }
}

?>
