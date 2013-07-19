<?php

// Function checks if User has enough TechLevel to do this Element
function IsTechnologieAccessible($_User, $planet, $Element)
{
	global $_Vars_Requirements, $_Vars_GameElements;

	if(isset($_Vars_Requirements[$Element]))
	{
		$enabled = true;
		foreach($_Vars_Requirements[$Element] as $ElementID => $ElementLevel)
		{ 
			if(@$_User[$_Vars_GameElements[$ElementID]] && $_User[$_Vars_GameElements[$ElementID]] >= $ElementLevel)
			{
				
			}
			else if($planet[$_Vars_GameElements[$ElementID]] && $planet[$_Vars_GameElements[$ElementID]] >= $ElementLevel)
			{
				$enabled = true;
			}
			else
			{
				return false;
			}
		}
		return $enabled;
	}
	else
	{
		return true;
	}
}

?>