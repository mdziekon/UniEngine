<?php

function SortUserPlanets($CurrentUser)
{
    $Sort = $CurrentUser['planet_sort'];
    $Order = $CurrentUser['planet_sort_order'];

    if($Sort == 0)
    {
        $Sort = '`id`';
    }
    elseif($Sort == 1)
    {
        $Sort = '`galaxy`, `system`, `planet`, `planet_type`';
    }
    elseif($Sort == 2)
    {
        $Sort = '`name`';
    }
    if($Order == 0)
    {
        $Order = 'ASC';
    }
    else
    {
        $Order = 'DESC';
    }

    $QryGetPlanets = "SELECT `id`, `name`, `galaxy`, `system`, `planet`, `planet_type` FROM {{table}} WHERE `id_owner` = '{$CurrentUser['id']}' ORDER BY {$Sort} {$Order};";
    return doquery($QryGetPlanets, 'planets');
}

?>
