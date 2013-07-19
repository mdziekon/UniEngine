<?php

function GetElementPrice($_User, $planet, $Element, $userfactor = true)
{
	global $_Vars_Prices, $_Vars_GameElements, $_Lang;

	if($userfactor)
	{
		$level = ($planet[$_Vars_GameElements[$Element]]) ? $planet[$_Vars_GameElements[$Element]] : $_User[$_Vars_GameElements[$Element]];
	}

	$array = array
	(
		'metal'			=> $_Lang["Metal"],
		'crystal'		=> $_Lang["Crystal"],
		'deuterium'		=> $_Lang["Deuterium"],
		'energy_max'	=> $_Lang["Energy"]
	);

	$text = $_Lang['Requires'] . ": ";
	foreach($array as $ResType => $ResTitle)
	{
		if($_Vars_Prices[$Element][$ResType] != 0)
		{
			$text .= $ResTitle . ": ";
			if($userfactor)
			{
				$cost = floor($_Vars_Prices[$Element][$ResType] * pow($_Vars_Prices[$Element]['factor'], $level));
			}
			else
			{
				$cost = floor($_Vars_Prices[$Element][$ResType]);
			}
			if($cost > $planet[$ResType])
			{
				$text .= "<b style=\"color:red;\"> <t title=\"-".prettyNumber($cost - $planet[$ResType])."\">";
				$text .= "<span class=\"noresources\">".prettyNumber($cost)."</span></t></b> ";
			}
			else
			{
				$text .= "<b style=\"color:lime;\"> <span class=\"noresources\">".prettyNumber($cost)."</span></b> ";
			}
		}
	}
	return $text;
}

?>