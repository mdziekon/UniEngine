<?php

define('INSIDE', true);

$_EnginePath = './';
include($_EnginePath.'common.php');

	loggedCheck();

	includeLang('fleetshortcut');

	$Mode = $_GET['mode'];
	$ID = intval($_GET['id']);

	if(empty($Mode))
	{
		$Shortcuts = doquery("SELECT {{table}}.*, IF(`planets`.`id` > 0, `planets`.`name`, '') AS `name`, IF(`planets`.`id` > 0, `planets`.`galaxy`, {{table}}.galaxy) AS `galaxy`, IF(`planets`.`id` > 0, `planets`.`system`, {{table}}.system) AS `system`, IF(`planets`.`id` > 0, `planets`.`planet`, {{table}}.planet) AS `planet`, IF(`planets`.`id` > 0, `planets`.`planet_type`, {{table}}.type) AS `planet_type` FROM {{table}} LEFT JOIN {{prefix}}planets as `planets` ON `planets`.`id` = {{table}}.`id_planet` WHERE {{table}}.`id_owner` = {$_User['id']} ORDER BY {{table}}.id ASC;", 'fleet_shortcuts');

		if(mysql_num_rows($Shortcuts) > 0)
		{
			while($Data = mysql_fetch_assoc($Shortcuts))
			{
				$_Lang['shortcuts_list'] .= '<option value="'.$Data['id'].'">'.((!empty($Data['own_name']) ? $Data['own_name'].' - ' : '')).$Data['name'].(($Data['planet_type'] == 3) ? ' ('.$_Lang['moon_sign'].')' : (($Data['planet_type'] == 2) ? ' ('.$_Lang['debris_sign'].')' : ' ('.$_Lang['planet_sign'].')')).' ['.$Data['galaxy'].':'.$Data['system'].':'.$Data['planet'].']</option>';
			}
		}
		else
		{
			$_Lang['shortcuts_list'] = '<option>'.$_Lang['no_shortcuts'].'</option>';
		}

		$page = parsetemplate(gettemplate('fleetshortcut_overview'), $_Lang);
	}
	else
	{
		switch($Mode)
		{
			case 'add':
				if($_POST['action'] == 'add')
				{
					$Name = trim($_POST['name']);
					$Galaxy = intval($_POST['galaxy']);
					$System = intval($_POST['system']);
					$Planet = intval($_POST['planet']);
					$Type = intval($_POST['type']);
					if(in_array($Type, array(1,2,3)) AND $Galaxy > 0 AND $System > 0 AND $Planet > 0 AND $Galaxy <= MAX_GALAXY_IN_WORLD AND $System <= MAX_SYSTEM_IN_GALAXY AND $Planet <= (MAX_PLANET_IN_SYSTEM + 1))
					{
						$TargetID = '0';
						if($Type == 1 OR $Type == 3)
						{
							$SelectTarget = doquery("SELECT `id` FROM {{table}} WHERE `galaxy` = {$Galaxy} AND `system` = {$System} AND `planet` = {$Planet} AND `planet_type` = {$Type} LIMIT 1;", 'planets', true);
							if($SelectTarget['id'] > 0)
							{
								$TargetID = $SelectTarget['id'];
							}
						}

						if(!empty($Name))
						{
							if(!preg_match('/^[a-zA-Z0-9\_\-\ ]{1,}$/D', $Name))
							{
								message($_Lang['Forbidden_signs_in_name'], $_Lang['Adding_shortcut'], 'fleetshortcut.php?mode=add', 2);
							}
						}

						$SelectTarget = doquery("SELECT `id` FROM {{table}} WHERE `galaxy` = {$Galaxy} AND `system` = {$System} AND `planet` = {$Planet} AND `type` = {$Type} AND `id_owner` = {$_User['id']} LIMIT 1;", 'fleet_shortcuts', true);
						if($SelectTarget['id'] > 0)
						{
							message($_Lang['That_target_already_exists'], $_Lang['Adding_shortcut'], 'fleetshortcut.php?mode=add', 2);
						}

						doquery("INSERT INTO {{table}} VALUES (NULL, {$_User['id']}, {$TargetID}, {$Galaxy}, {$System}, {$Planet}, {$Type}, '{$Name}');", 'fleet_shortcuts');
						message($_Lang['Shortcut_hasbeen_added'], $_Lang['Adding_shortcut'],'fleetshortcut.php', 2);
					}
					else
					{
						message($_Lang['Bad_coordinates'], $_Lang['Adding_shortcut'], 'fleetshortcut.php?mode=add', 2);
					}
				}
				else
				{
					$_Lang['Action_shortcut'] = $_Lang['Adding_shortcut'];
					$_Lang['Action']= $_Lang['Add'];
					$_Lang['post_action'] = 'add';

				$page = parsetemplate(gettemplate('fleetshortcut_add_edit'), $_Lang);
				}
				break;
			case 'delete':
				$ID = intval($_GET['id']);
				$SelectLink = doquery("SELECT `id_owner` FROM {{table}} WHERE `id` = {$ID} LIMIT 1;", 'fleet_shortcuts', true);
				if($SelectLink['id_owner'] > 0)
				{
					if($SelectLink['id_owner'] == $_User['id'])
					{
						doquery("DELETE FROM {{table}} WHERE `id` = {$ID};", 'fleet_shortcuts');
						message($_Lang['Link_hasbeen_deleted'], $_Lang['Deleting_shortcut'],'fleetshortcut.php', 2);
					}
					else
					{
						message($_Lang['This_shortcut_is_not_yours'], $_Lang['Deleting_shortcut'],'fleetshortcut.php', 2);
					}
				}
				else
				{
					message($_Lang['Bad_ID_given'], $_Lang['Deleting_shortcut'],'fleetshortcut.php', 2);
				}
				break;
			case 'edit':
				$ID = intval($_GET['id']);

				$SelectLink = doquery("SELECT * FROM {{table}} WHERE `id` = {$ID} LIMIT 1;", 'fleet_shortcuts', true);
				if($SelectLink['id_owner'] > 0)
				{
					if($SelectLink['id_owner'] == $_User['id'])
					{
						if($_POST['action'] == 'edit')
						{
							$Name = trim($_POST['name']);
							$Galaxy = intval($_POST['galaxy']);
							$System = intval($_POST['system']);
							$Planet = intval($_POST['planet']);
							$Type = intval($_POST['type']);
							if(in_array($Type, array(1,2,3)) AND $Galaxy > 0 AND $System > 0 AND $Planet > 0 AND $Galaxy <= MAX_GALAXY_IN_WORLD AND $System <= MAX_SYSTEM_IN_GALAXY AND $Planet <= (MAX_PLANET_IN_SYSTEM + 1))
							{
								$TargetID = '0';
								if($Type == 1 OR $Type == 3)
								{
									$SelectTarget = doquery("SELECT `id` FROM {{table}} WHERE `galaxy` = {$Galaxy} AND `system` = {$System} AND `planet` = {$Planet} AND `planet_type` = {$Type} LIMIT 1;", 'planets', true);
									if($SelectTarget['id'] > 0)
									{
										$TargetID = $SelectTarget['id'];
									}
								}

								if(!empty($Name))
								{
									if(!preg_match('/^[a-zA-Z0-9\_\-\ ]{1,}$/D', $Name))
									{
										message($_Lang['Forbidden_signs_in_name'], $_Lang['Editing_shortcut'], 'fleetshortcut.php?mode=edit&id='.$ID, 2);
									}
								}

								$SelectTarget = doquery("SELECT `id` FROM {{table}} WHERE `galaxy` = {$Galaxy} AND `system` = {$System} AND `planet` = {$Planet} AND `type` = {$Type} AND `id_owner` = {$_User['id']} LIMIT 1;", 'fleet_shortcuts', true);
								if($SelectTarget['id'] > 0 AND $SelectTarget['id'] != $ID)
								{
									message($_Lang['That_target_already_exists'], $_Lang['Editing_shortcut'], 'fleetshortcut.php?mode=edit&id='.$ID, 2);
								}

								doquery("UPDATE {{table}} SET `id_planet` = {$TargetID}, `galaxy` = {$Galaxy}, `system` = {$System}, `planet` = {$Planet}, `type` = {$Type}, `own_name` = '{$Name}' WHERE `id` = {$ID};", 'fleet_shortcuts');
								message($_Lang['Shortcut_hasbeen_saved'], $_Lang['Editing_shortcut'], 'fleetshortcut.php', 2);
							}
							else
							{
								message($_Lang['Bad_coordinates'], $_Lang['Editing_shortcut'], 'fleetshortcut.php?mode=edit&id='.$ID, 2);
							}
						}
						else
						{
							$_Lang['Action_shortcut'] = $_Lang['Editing_shortcut'];
							$_Lang['Action'] = $_Lang['Edit'];
							$_Lang['post_action'] = 'edit';
							$_Lang['edit_id'] = $ID;
							$_Lang['set_name'] = $SelectLink['own_name'];
							$_Lang['set_galaxy'] = $SelectLink['galaxy'];
							$_Lang['set_system'] = $SelectLink['system'];
							$_Lang['set_planet'] = $SelectLink['planet'];
							switch($SelectLink['type'])
							{
								case 1:
									$_Lang['select_planet'] = 'selected';
									break;
								case 2:
									$_Lang['select_debris'] = 'selected';
									break;
								case 3:
									$_Lang['select_moon'] = 'selected';
									break;
							}

							$page = parsetemplate(gettemplate('fleetshortcut_add_edit'), $_Lang);
						} 
					}
					else
					{
						message($_Lang['This_shortcut_is_not_yours'], $_Lang['Editing_shortcut'],'fleetshortcut.php', 2);
					} 
				}
				else
				{
					message($_Lang['Bad_ID_given'], $_Lang['Editing_shortcut'],'fleetshortcut.php', 2);
				}
				break;
		}
	}

	display($page, $_Lang['Title']);

?>