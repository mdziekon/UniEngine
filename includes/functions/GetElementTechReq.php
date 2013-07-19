<?php

function GetElementTechReq($_User, $planet, $Element, $OnlyDivs = false)
{
	global $_Vars_Requirements, $_Vars_GameElements, $_Vars_ElementCategories, $_Lang, $_SkinPath;
	static $TPL;

	if(isset($_Vars_Requirements[$Element]))
	{
		foreach($_Vars_Requirements[$Element] as $ElementID => $ElementLevel)
		{
			if(@$_User[$_Vars_GameElements[$ElementID]] AND $_User[$_Vars_GameElements[$ElementID]] >= $ElementLevel)
			{
				// It's Good, but save it
				$RequiredDone[$ElementID] = array($ElementLevel, $_User[$_Vars_GameElements[$ElementID]]);
			}
			else if($planet[$_Vars_GameElements[$ElementID]] AND $planet[$_Vars_GameElements[$ElementID]] >= $ElementLevel)
			{
				// It's Good, but save it
				$RequiredDone[$ElementID] = array($ElementLevel, $planet[$_Vars_GameElements[$ElementID]]);
			}
			else
			{
				if(in_array($ElementID, $_Vars_ElementCategories['tech']))
				{
					$HasLevel = $_User[$_Vars_GameElements[$ElementID]];
				}
				else
				{
					$HasLevel = $planet[$_Vars_GameElements[$ElementID]];
				}
				$Required[$ElementID] = array($ElementLevel, $HasLevel);
			}
		}
	}

	if(!empty($Required))
	{
		if(!empty($RequiredDone))
		{
			foreach($RequiredDone as $Key => $Data)
			{
				$Required[$Key] = $Data;
			}
			asort($Required);
		}
		if(empty($TPL))
		{
			$TPL['main'] = gettemplate('_function_getelementtechreq_main');
			$TPL['divs'] = gettemplate('_function_getelementtechreq_divs');
		}

		foreach($Required as $ElementID => $Data)
		{
			if($Data[1] >= $Data[0])
			{
				$Color = 'lime';
			}
			else
			{
				$Color = 'red';
			}
			$_Lang['Insert_TechReqDivs'] .= parsetemplate($TPL['divs'], array('ID' => $ElementID, 'Name' => $_Lang['tech'][$ElementID], 'skinpath' => $_SkinPath, 'Color' => $Color, 'CLevel' => $Data[1], 'NLevel' => $Data[0]));
		}
		if($OnlyDivs)
		{
			$ToReturn = $_Lang['Insert_TechReqDivs'];
			$_Lang['Insert_TechReqDivs'] = '';
			return $ToReturn;
		}

		$Return = parsetemplate($TPL['main'], $_Lang);
		$_Lang['Insert_TechReqDivs'] = '';
	}
	return $Return;
}

?>