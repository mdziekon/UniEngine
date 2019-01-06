<?php

function CreatePaginationArray($TotalCount, $PerPageCount, $ThisPage, $MaxPages)
{
    // Initialization
    $Return                            = array();
    $Return['hasPrev']                = true;
    $Return['hasNext']                = true;
    $Return['hasLeftMostBreak']        = true;
    $Return['hasRightMostBreak']    = true;
    $IsOnEdge                        = false;
    $AddToOneSide                    = false;

    // Sanitize vars
    $ThisPage = round($ThisPage);

    // Do necessary calculations
    $LastPage = ceil($TotalCount / $PerPageCount);

    if($ThisPage <= 1)
    {
        $ThisPage = 1;
        $Return['hasPrev'] = false;
        $IsOnEdge = true;
    }
    if($ThisPage >= $LastPage)
    {
        $ThisPage = $LastPage;
        $Return['hasNext'] = false;
        $IsOnEdge = true;
    }
    if($MaxPages < 3)
    {
        // We have to show at least 3 pages
        $MaxPages = 3;
    }

    $ToShow = $MaxPages - 3;
    if(($ToShow % 2) == 1 AND $ToShow > 0)
    {
        // If $ToShow is even number, cut one down
        $ToShow -= 1;
        $AddToOneSide = true;
    }

    $LeftSide = $RightSide = ($ToShow / 2);
    if($AddToOneSide)
    {
        if($ThisPage > (($LastPage + 1) / 2))
        {
            $LeftSide += 1;
        }
        else
        {
            $RightSide += 1;
        }
    }
    $LeftMost = ($ThisPage - $LeftSide);
    $RightMost = ($ThisPage + $RightSide);
    $StartToLeftMostDistance = $LeftMost - 1;
    $EndToRightMostDistance = $LastPage - $RightMost;
    if($StartToLeftMostDistance <= 1)
    {
        $Return['hasLeftMostBreak'] = false;
        if($StartToLeftMostDistance < 1)
        {
            $Value = (($StartToLeftMostDistance - 1) * -1);
            $RightSide += (($StartToLeftMostDistance - 1) * -1);
            $RightMost += $Value;
        }
    }
    if($EndToRightMostDistance <= 1)
    {
        $Return['hasRightMostBreak'] = false;
        if($EndToRightMostDistance < 1)
        {
            $Value = (($EndToRightMostDistance - 1) * -1);
            $LeftSide += $Value;
            $LeftMost -= $Value;
        }
    }

    // Generate Elements Array
    $Return['pages'][] = 1;
    if($Return['hasLeftMostBreak'] AND $MaxPages < $LastPage)
    {
        $Return['pages'][] = '_';
    }
    for($i = $LeftMost; $i < $ThisPage; $i += 1)
    {
        if($LeftSide <= 0)
        {
            break;
        }
        if($i <= 1)
        {
            $i = 1;
            continue;
        }
        $Return['pages'][] = $i;
        $LeftSide -= 1;
    }
    if($IsOnEdge === false)
    {
        $Return['pages'][] = $ThisPage;
    }
    for($i = ($ThisPage + 1); $i < $LastPage; $i += 1)
    {
        if($RightSide <= 0)
        {
            break;
        }
        $Return['pages'][] = $i;
        $RightSide -= 1;
    }
    if($Return['hasRightMostBreak'] AND $MaxPages < $LastPage)
    {
        $Return['pages'][] = '_';
    }
    if($LastPage != 1)
    {
        $Return['pages'][] = $LastPage;
    }

    return $Return;
}

function ParsePaginationArray($PaginationArray, $Page, $ElementTemplate, $ViewOptions = array())
{
    if(empty($ElementTemplate))
    {
        return array();
    }

    if(!empty($PaginationArray['pages']))
    {
        // Initialization
        global $_Lang;
        if(!isset($_Lang['Pagination_Prev']))
        {
            $_Lang['Pagination_Prev'] = '&#171;';
        }
        if(!isset($_Lang['Pagination_Next']))
        {
            $_Lang['Pagination_Next'] = '&#187;';
        }
        $ReplaceFind = array('{$Classes}', '{$Value}', '{$ShowValue}');
        $Return = array();

        if(isset($ViewOptions['OffsetValues']) && $ViewOptions['OffsetValues'] === true)
        {
            $ValuesAsOffset = true;
        }
        else
        {
            $ValuesAsOffset = false;
        }

        // Parsing
        if(isset($PaginationArray['hasPrev']) && $PaginationArray['hasPrev'])
        {
            $Return[] = str_replace($ReplaceFind, array('', ($ValuesAsOffset ? -1 : ($Page - 1)), $_Lang['Pagination_Prev']), $ElementTemplate);
        }
        foreach($PaginationArray['pages'] as $PageNo)
        {
            if($PageNo != '_')
            {
                $Return[] = str_replace
                (
                    $ReplaceFind,
                    array
                    (
                        (($PageNo == $Page) ? $ViewOptions['CurrentPage_Classes'] : ''),
                        ($ValuesAsOffset ? ($PageNo - $Page) : $PageNo),
                        $PageNo
                    ),
                    $ElementTemplate
                );
            }
            else
            {
                $Return[] = $ViewOptions['Breaker_View'];
            }
        }
        if(isset($PaginationArray['hasNext']) && $PaginationArray['hasNext'])
        {
            $Return[] = str_replace($ReplaceFind, array('', ($ValuesAsOffset ? 1 : ($Page + 1)), $_Lang['Pagination_Next']), $ElementTemplate);
        }

        return $Return;
    }
    return array();
}

?>
