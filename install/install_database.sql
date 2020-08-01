--
-- Table structure for table `achievements_stats`
--

CREATE TABLE IF NOT EXISTS `{prefix}_achievements_stats` (
  `A_UserID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ustat_raids_won` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ustat_raids_draw` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ustat_raids_lost` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ustat_raids_acs_won` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ustat_raids_inAlly` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ustat_raids_missileAttack` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ustat_moons_destroyed` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ustat_moons_created` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ustat_other_expeditions_count` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_202` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_203` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_204` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_205` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_206` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_207` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_208` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_209` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_210` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_211` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_212` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_213` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_214` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_215` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_216` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_217` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_218` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_219` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_220` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_221` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_222` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_223` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_224` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_401` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_402` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_403` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_404` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_405` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_406` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_407` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyed_408` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_202` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_203` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_204` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_205` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_206` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_207` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_208` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_209` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_210` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_211` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_212` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_213` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_214` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_215` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_216` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_217` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_218` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_219` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_220` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_221` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_222` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_223` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_224` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_401` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_402` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_403` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_404` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_405` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_406` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_407` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lost_408` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_202` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_203` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_204` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_205` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_206` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_207` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_208` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_209` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_210` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_211` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_212` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_213` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_214` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_215` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_216` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_217` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_218` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_219` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_220` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_221` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_222` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_223` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_224` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_401` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_402` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_403` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_404` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_405` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_406` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_407` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_408` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_502` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_503` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`A_UserID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `acs`
--

CREATE TABLE IF NOT EXISTS `{prefix}_acs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_polish_ci NOT NULL,
  `main_fleet_id` bigint(20) unsigned NOT NULL,
  `owner_id` int(10) unsigned NOT NULL,
  `users` text COLLATE utf8_polish_ci NOT NULL,
  `fleets_id` text COLLATE utf8_polish_ci NOT NULL,
  `user_joined` text COLLATE utf8_polish_ci NOT NULL,
  `invited_users` tinyint(1) unsigned NOT NULL,
  `fleets_count` tinyint(2) unsigned NOT NULL,
  `start_time_org` int(10) unsigned NOT NULL,
  `start_time` int(10) unsigned NOT NULL,
  `end_target_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `end_galaxy` tinyint(3) unsigned NOT NULL,
  `end_system` smallint(5) unsigned NOT NULL,
  `end_planet` tinyint(3) unsigned NOT NULL,
  `end_type` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `main_fleet_id` (`main_fleet_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `alliance`
--

CREATE TABLE IF NOT EXISTS `{prefix}_alliance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ally_name` varchar(35) CHARACTER SET utf8 DEFAULT NULL,
  `ally_tag` varchar(8) CHARACTER SET utf8 DEFAULT '',
  `ally_owner` int(10) unsigned NOT NULL DEFAULT '0',
  `ally_register_time` int(10) unsigned NOT NULL DEFAULT '0',
  `ally_description` text CHARACTER SET utf8,
  `ally_web` text CHARACTER SET utf8,
  `ally_web_reveal` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ally_text` text CHARACTER SET utf8,
  `ally_image` text CHARACTER SET utf8,
  `ally_request` text CHARACTER SET utf8,
  `ally_request_notallow` tinyint(1) NOT NULL DEFAULT '0',
  `ally_ranks` text CHARACTER SET utf8,
  `ally_new_rank_id` int(10) unsigned NOT NULL DEFAULT '1',
  `ally_members` int(10) unsigned NOT NULL DEFAULT '0',
  `ally_ChatRoom_ID` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ally_invites`
--

CREATE TABLE IF NOT EXISTS `{prefix}_ally_invites` (
  `AllyID` int(10) unsigned NOT NULL,
  `OwnerID` int(10) unsigned NOT NULL,
  `SenderID` int(10) unsigned NOT NULL,
  `Date` int(10) unsigned NOT NULL,
  `State` tinyint(4) NOT NULL DEFAULT '1',
  KEY `AllyID` (`AllyID`),
  KEY `OwnerID` (`OwnerID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ally_pacts`
--

CREATE TABLE IF NOT EXISTS `{prefix}_ally_pacts` (
  `AllyID_Sender` int(10) unsigned NOT NULL,
  `AllyID_Owner` int(10) unsigned NOT NULL,
  `Date` int(10) unsigned NOT NULL,
  `Type` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `Active` tinyint(1) NOT NULL DEFAULT '0',
  `Change_Sender` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Change_Owner` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`AllyID_Sender`,`AllyID_Owner`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ally_wars`
--

CREATE TABLE IF NOT EXISTS `{prefix}_ally_wars` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Allys1_IDs` text NOT NULL,
  `Allys2_IDs` text NOT NULL,
  `Date` int(10) unsigned NOT NULL,
  `Active` tinyint(1) NOT NULL,
  `Type` tinyint(1) unsigned NOT NULL,
  `End_Point` bigint(20) unsigned NOT NULL,
  `Summary_1` bigint(20) unsigned NOT NULL,
  `Summary_2` bigint(20) unsigned NOT NULL,
  `Reports` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bans`
--

CREATE TABLE IF NOT EXISTS `{prefix}_bans` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `StartTime` int(10) unsigned NOT NULL,
  `EndTime` int(10) unsigned NOT NULL,
  `Reason` text NOT NULL,
  `GiverID` bigint(20) unsigned NOT NULL,
  `With_Vacation` tinyint(1) NOT NULL DEFAULT '1',
  `Active` tinyint(1) NOT NULL DEFAULT '1',
  `Expired` tinyint(1) NOT NULL DEFAULT '0',
  `Removed` tinyint(1) NOT NULL DEFAULT '0',
  `RemoveDate` int(10) unsigned NOT NULL DEFAULT '0',
  `Fleets_Retreated_Own` tinyint(1) NOT NULL DEFAULT '0',
  `Fleets_Retreated_Others` tinyint(1) NOT NULL DEFAULT '0',
  `BlockadeOn_CookieStyle` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `battle_reports`
--

CREATE TABLE IF NOT EXISTS `{prefix}_battle_reports` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `time` int(10) unsigned NOT NULL,
  `id_owner1` text CHARACTER SET utf8 NOT NULL,
  `id_owner2` text CHARACTER SET utf8 NOT NULL,
  `report` text CHARACTER SET utf8 NOT NULL,
  `disallow_attacker` enum('0','1') CHARACTER SET utf8 NOT NULL DEFAULT '0',
  `Hash` char(32) CHARACTER SET utf8 NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `Hash` (`Hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `buddy`
--

CREATE TABLE IF NOT EXISTS `{prefix}_buddy` (
  `sender` int(10) unsigned NOT NULL,
  `owner` int(10) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `text` text CHARACTER SET utf8 NOT NULL,
  `date` int(10) unsigned NOT NULL,
  PRIMARY KEY (`sender`,`owner`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE IF NOT EXISTS `{prefix}_chat_messages` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `RID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'RoomID',
  `UID` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UserID',
  `TimeStamp_Add` int(10) unsigned NOT NULL DEFAULT '0',
  `TimeStamp_Edit` int(10) unsigned NOT NULL DEFAULT '0',
  `Text` text COLLATE utf8_polish_ci NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `RID` (`RID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `chat_online`
--

CREATE TABLE IF NOT EXISTS `{prefix}_chat_online` (
  `RID` int(10) unsigned NOT NULL COMMENT 'RoomID',
  `UID` int(10) unsigned NOT NULL COMMENT 'UserID',
  `LastOnline` int(10) unsigned NOT NULL,
  PRIMARY KEY (`RID`,`UID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_rooms`
--

CREATE TABLE IF NOT EXISTS `{prefix}_chat_rooms` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `AccessType` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `AccessCheck` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `{prefix}_config` (
  `config_name` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `config_value` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`config_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `declarations`
--

CREATE TABLE IF NOT EXISTS `{prefix}_declarations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `users` text COLLATE utf8_polish_ci NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `reason` tinyint(3) unsigned NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `all_present_users` text COLLATE utf8_polish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `deleted_users`
--

CREATE TABLE IF NOT EXISTS `{prefix}_deleted_users` (
  `id` int(10) unsigned NOT NULL,
  `username` varchar(64) COLLATE utf8_polish_ci NOT NULL,
  `password` char(32) CHARACTER SET utf8 NOT NULL,
  `email` varchar(64) COLLATE utf8_polish_ci NOT NULL,
  `email2` varchar(64) COLLATE utf8_polish_ci NOT NULL,
  `last_ip` varchar(16) CHARACTER SET utf8 NOT NULL,
  `reg_ip` varchar(16) CHARACTER SET utf8 NOT NULL,
  `register_time` int(10) unsigned NOT NULL,
  `last_online` int(10) unsigned NOT NULL,
  `user_agent` text COLLATE utf8_polish_ci NOT NULL,
  `delete_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `errors`
--

CREATE TABLE IF NOT EXISTS `{prefix}_errors` (
  `error_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `error_sender` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '0',
  `error_time` int(10) unsigned NOT NULL DEFAULT '0',
  `error_text` text CHARACTER SET utf8,
  PRIMARY KEY (`error_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `fleets`
--

CREATE TABLE IF NOT EXISTS `{prefix}_fleets` (
  `fleet_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `fleet_owner` int(10) unsigned NOT NULL DEFAULT '0',
  `fleet_mission` smallint(6) NOT NULL DEFAULT '0',
  `fleet_amount` bigint(20) NOT NULL DEFAULT '0',
  `fleet_array` text CHARACTER SET utf8,
  `fleet_start_time` int(10) unsigned NOT NULL DEFAULT '0',
  `fleet_start_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `fleet_start_galaxy` tinyint(4) NOT NULL DEFAULT '0',
  `fleet_start_system` smallint(6) NOT NULL DEFAULT '0',
  `fleet_start_planet` tinyint(4) NOT NULL DEFAULT '0',
  `fleet_start_type` tinyint(4) NOT NULL DEFAULT '0',
  `fleet_end_time` int(10) unsigned NOT NULL DEFAULT '0',
  `fleet_end_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `fleet_end_id_galaxy` bigint(20) unsigned NOT NULL DEFAULT '0',
  `fleet_end_stay` int(10) unsigned NOT NULL DEFAULT '0',
  `fleet_end_galaxy` tinyint(4) NOT NULL DEFAULT '0',
  `fleet_end_system` smallint(6) NOT NULL DEFAULT '0',
  `fleet_end_planet` tinyint(4) NOT NULL DEFAULT '0',
  `fleet_end_type` tinyint(4) NOT NULL DEFAULT '0',
  `fleet_resource_metal` bigint(20) NOT NULL DEFAULT '0',
  `fleet_resource_crystal` bigint(20) NOT NULL DEFAULT '0',
  `fleet_resource_deuterium` bigint(20) NOT NULL DEFAULT '0',
  `fleet_target_owner` int(10) unsigned NOT NULL DEFAULT '0',
  `fleet_mess` smallint(6) NOT NULL DEFAULT '0',
  `fleet_send_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`fleet_id`),
  KEY `fleet_owner` (`fleet_owner`),
  KEY `fleet_target_owner` (`fleet_target_owner`),
  KEY `fleet_start_id` (`fleet_start_id`),
  KEY `fleet_end_id` (`fleet_end_id`),
  KEY `fleet_end_id_galaxy` (`fleet_end_id_galaxy`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `fleet_archive`
--

CREATE TABLE IF NOT EXISTS `{prefix}_fleet_archive` (
  `Fleet_ID` bigint(20) unsigned NOT NULL,
  `Fleet_Owner` int(10) unsigned NOT NULL,
  `Fleet_Mission` tinyint(3) unsigned NOT NULL,
  `Fleet_Mission_Changed` tinyint(1) NOT NULL DEFAULT '0',
  `Fleet_Array` text NOT NULL,
  `Fleet_Array_Changes` text NOT NULL,
  `Fleet_Time_Send` int(10) unsigned NOT NULL,
  `Fleet_Time_Start` int(10) unsigned NOT NULL,
  `Fleet_Time_Stay` int(10) unsigned NOT NULL DEFAULT '0',
  `Fleet_Time_End` int(10) unsigned NOT NULL DEFAULT '0',
  `Fleet_Time_ACSAdd` int(10) unsigned NOT NULL DEFAULT '0',
  `Fleet_Start_ID` bigint(20) unsigned NOT NULL,
  `Fleet_Start_Galaxy` tinyint(3) unsigned NOT NULL,
  `Fleet_Start_System` smallint(5) unsigned NOT NULL,
  `Fleet_Start_Planet` tinyint(3) unsigned NOT NULL,
  `Fleet_Start_Type` tinyint(3) unsigned NOT NULL,
  `Fleet_Start_Type_Changed` tinyint(1) NOT NULL DEFAULT '0',
  `Fleet_Start_ID_Changed` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Fleet_Start_Res_Metal` double(132,8) unsigned NOT NULL DEFAULT '0.00000000',
  `Fleet_Start_Res_Crystal` double(132,8) unsigned NOT NULL DEFAULT '0.00000000',
  `Fleet_Start_Res_Deuterium` double(132,8) unsigned NOT NULL DEFAULT '0.00000000',
  `Fleet_End_ID` bigint(20) unsigned NOT NULL,
  `Fleet_End_ID_Galaxy` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Fleet_End_Galaxy` tinyint(3) unsigned NOT NULL,
  `Fleet_End_System` smallint(5) unsigned NOT NULL,
  `Fleet_End_Planet` tinyint(3) unsigned NOT NULL,
  `Fleet_End_Type` tinyint(3) unsigned NOT NULL,
  `Fleet_End_Type_Changed` tinyint(1) NOT NULL DEFAULT '0',
  `Fleet_End_ID_Changed` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Fleet_End_Res_Metal` double(132,8) unsigned NOT NULL DEFAULT '0.00000000',
  `Fleet_End_Res_Crystal` double(132,8) unsigned NOT NULL DEFAULT '0.00000000',
  `Fleet_End_Res_Deuterium` double(132,8) unsigned NOT NULL DEFAULT '0.00000000',
  `Fleet_End_Owner` int(10) unsigned NOT NULL DEFAULT '0',
  `Fleet_End_Owner_IdleHours` smallint(5) unsigned NOT NULL DEFAULT '0',
  `Fleet_Calculated_Mission` tinyint(1) NOT NULL DEFAULT '0',
  `Fleet_Calculated_Mission_Time` int(10) unsigned NOT NULL DEFAULT '0',
  `Fleet_Calculated_ComeBack` tinyint(1) NOT NULL DEFAULT '0',
  `Fleet_Calculated_ComeBack_Time` int(10) unsigned NOT NULL DEFAULT '0',
  `Fleet_Destroyed` tinyint(1) NOT NULL DEFAULT '0',
  `Fleet_Destroyed_Reason` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Fleet_TurnedBack` tinyint(1) NOT NULL DEFAULT '0',
  `Fleet_TurnedBack_Time` int(10) unsigned NOT NULL DEFAULT '0',
  `Fleet_TurnedBack_EndTime` int(10) unsigned NOT NULL DEFAULT '0',
  `Fleet_ACSID` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Fleet_ReportID` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Fleet_DefenderReportIDs` text NOT NULL,
  `Fleet_Info_HadSameIP_Ever` tinyint(1) NOT NULL DEFAULT '0',
  `Fleet_Info_HadSameIP_Ever_Filtred` tinyint(1) NOT NULL DEFAULT '0',
  `Fleet_Info_HadSameIP_OnSend` tinyint(1) NOT NULL DEFAULT '0',
  `Fleet_Info_HasLostShips` tinyint(1) NOT NULL DEFAULT '0',
  `Fleet_Info_UsedTeleport` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Fleet_ID`),
  KEY `Fleet_Owner` (`Fleet_Owner`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fleet_shortcuts`
--

CREATE TABLE IF NOT EXISTS `{prefix}_fleet_shortcuts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_owner` int(10) unsigned NOT NULL,
  `id_planet` bigint(20) unsigned NOT NULL,
  `galaxy` tinyint(3) unsigned NOT NULL,
  `system` smallint(5) unsigned NOT NULL,
  `planet` tinyint(3) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `own_name` varchar(50) COLLATE utf8_polish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `galaxy`
--

CREATE TABLE IF NOT EXISTS `{prefix}_galaxy` (
  `galaxy_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `galaxy` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `system` smallint(5) unsigned NOT NULL DEFAULT '0',
  `planet` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `id_planet` bigint(20) unsigned NOT NULL DEFAULT '0',
  `metal` bigint(20) NOT NULL DEFAULT '0',
  `crystal` bigint(20) NOT NULL DEFAULT '0',
  `id_moon` bigint(20) unsigned NOT NULL DEFAULT '0',
  `hide_planet` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`galaxy_id`),
  UNIQUE KEY `id_planet` (`id_planet`),
  KEY `galaxy` (`galaxy`),
  KEY `system` (`system`),
  KEY `planet` (`planet`),
  KEY `id_moon` (`id_moon`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ignoresystem`
--

CREATE TABLE IF NOT EXISTS `{prefix}_ignoresystem` (
  `OwnerID` int(10) unsigned NOT NULL,
  `IgnoredID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`OwnerID`,`IgnoredID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_protection`
--

CREATE TABLE IF NOT EXISTS `{prefix}_login_protection` (
  `IP` char(32) NOT NULL,
  `Date` int(10) unsigned NOT NULL,
  `FailCount` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`IP`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mailchange`
--

CREATE TABLE IF NOT EXISTS `{prefix}_mailchange` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Date` int(10) unsigned NOT NULL,
  `UserID` int(10) unsigned NOT NULL,
  `OldMail` varchar(255) COLLATE utf8_polish_ci NOT NULL,
  `NewMail` varchar(255) COLLATE utf8_polish_ci NOT NULL,
  `ConfirmType` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ReConfirmSend` int(10) unsigned NOT NULL DEFAULT '0',
  `ConfirmHash` varchar(32) COLLATE utf8_polish_ci NOT NULL,
  `ConfirmHashNew` varchar(32) COLLATE utf8_polish_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE IF NOT EXISTS `{prefix}_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_owner` int(10) unsigned NOT NULL,
  `id_sender` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `type` smallint(3) unsigned NOT NULL,
  `from` varchar(20) COLLATE utf8_polish_ci NOT NULL,
  `subject` varchar(128) COLLATE utf8_polish_ci NOT NULL,
  `text` text COLLATE utf8_polish_ci NOT NULL,
  `read` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `Thread_ID` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Thread_IsLast` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_owner` (`id_owner`),
  KEY `id_sender` (`id_sender`),
  KEY `Thread_ID` (`Thread_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `nick_changelog`
--

CREATE TABLE IF NOT EXISTS `{prefix}_nick_changelog` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `new_username` varchar(64) NOT NULL,
  `old_username` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE IF NOT EXISTS `{prefix}_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `owner` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `priority` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `title` text CHARACTER SET utf8 NOT NULL,
  `text` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `planets`
--

CREATE TABLE IF NOT EXISTS `{prefix}_planets` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `id_owner` int(10) unsigned NOT NULL DEFAULT '0',
  `galaxy` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `system` smallint(5) unsigned NOT NULL DEFAULT '0',
  `planet` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `last_update` int(10) unsigned NOT NULL DEFAULT '0',
  `planet_type` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `abandon_time` int(10) unsigned NOT NULL DEFAULT '0',
  `buildQueue_firstEndTime` int(10) unsigned NOT NULL DEFAULT '0',
  `buildQueue` text CHARACTER SET utf8 NOT NULL,
  `techQueue` text COLLATE utf8_polish_ci NOT NULL,
  `techQueue_firstEndTime` int(10) unsigned NOT NULL DEFAULT '0',
  `shipyardQueue_additionalWorkTime` int(10) unsigned NOT NULL DEFAULT '0',
  `shipyardQueue` text CHARACTER SET utf8 NOT NULL,
  `image` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT 'normaltempplanet01',
  `diameter` int(10) unsigned NOT NULL DEFAULT '12800',
  `points` bigint(20) unsigned NOT NULL DEFAULT '0',
  `field_current` smallint(5) unsigned NOT NULL DEFAULT '0',
  `field_max` smallint(5) unsigned NOT NULL DEFAULT '163',
  `temp_min` smallint(6) NOT NULL DEFAULT '-17',
  `temp_max` smallint(6) NOT NULL DEFAULT '23',
  `metal` double(132,8) unsigned NOT NULL DEFAULT '0.00000000',
  `metal_perhour` bigint(20) unsigned NOT NULL DEFAULT '0',
  `metal_max` bigint(20) unsigned NOT NULL DEFAULT '100000',
  `crystal` double(132,8) unsigned NOT NULL DEFAULT '0.00000000',
  `crystal_perhour` bigint(20) unsigned NOT NULL DEFAULT '0',
  `crystal_max` bigint(20) unsigned NOT NULL DEFAULT '100000',
  `deuterium` double(132,8) unsigned NOT NULL DEFAULT '0.00000000',
  `deuterium_perhour` bigint(20) unsigned NOT NULL DEFAULT '0',
  `deuterium_max` bigint(20) unsigned NOT NULL DEFAULT '100000',
  `energy_used` bigint(20) NOT NULL DEFAULT '0',
  `energy_max` bigint(20) NOT NULL DEFAULT '0',
  `metal_mine` int(10) unsigned NOT NULL DEFAULT '0',
  `crystal_mine` int(10) unsigned NOT NULL DEFAULT '0',
  `deuterium_synthesizer` int(10) unsigned NOT NULL DEFAULT '0',
  `solar_plant` int(10) unsigned NOT NULL DEFAULT '0',
  `fusion_reactor` int(10) unsigned NOT NULL DEFAULT '0',
  `robotic_factory` int(10) unsigned NOT NULL DEFAULT '0',
  `nanite_factory` int(10) unsigned NOT NULL DEFAULT '0',
  `shipyard` int(10) unsigned NOT NULL DEFAULT '0',
  `metal_storage` int(10) unsigned NOT NULL DEFAULT '0',
  `crystal_storage` int(10) unsigned NOT NULL DEFAULT '0',
  `deuterium_tank` int(10) unsigned NOT NULL DEFAULT '0',
  `research_lab` int(10) unsigned NOT NULL DEFAULT '0',
  `terraformer` int(10) unsigned NOT NULL DEFAULT '0',
  `alliance_depot` int(10) unsigned NOT NULL DEFAULT '0',
  `missile_silo` int(10) unsigned NOT NULL DEFAULT '0',
  `small_cargo_ship` bigint(20) unsigned NOT NULL DEFAULT '0',
  `big_cargo_ship` bigint(20) unsigned NOT NULL DEFAULT '0',
  `light_fighter` bigint(20) unsigned NOT NULL DEFAULT '0',
  `heavy_fighter` bigint(20) unsigned NOT NULL DEFAULT '0',
  `cruiser` bigint(20) unsigned NOT NULL DEFAULT '0',
  `battleship` bigint(20) unsigned NOT NULL DEFAULT '0',
  `colony_ship` bigint(20) unsigned NOT NULL DEFAULT '0',
  `recycler` bigint(20) unsigned NOT NULL DEFAULT '0',
  `espionage_probe` bigint(20) unsigned NOT NULL DEFAULT '0',
  `bomber` bigint(20) unsigned NOT NULL DEFAULT '0',
  `solar_satellite` bigint(20) unsigned NOT NULL DEFAULT '0',
  `destroyer` bigint(20) unsigned NOT NULL DEFAULT '0',
  `deathstar` bigint(20) unsigned NOT NULL DEFAULT '0',
  `battlecruiser` bigint(20) unsigned NOT NULL DEFAULT '0',
  `orbital_station` bigint(20) unsigned NOT NULL DEFAULT '0',
  `mega_cargo_ship` bigint(20) unsigned NOT NULL DEFAULT '0',
  `annihilator` bigint(20) unsigned NOT NULL DEFAULT '0',
  `space_shuttle` bigint(20) unsigned NOT NULL DEFAULT '0',
  `space_carrier` bigint(20) unsigned NOT NULL DEFAULT '0',
  `hadron_bomber` bigint(20) unsigned NOT NULL DEFAULT '0',
  `plasmic_ship` bigint(20) unsigned NOT NULL DEFAULT '0',
  `proton_destroyer` bigint(20) unsigned NOT NULL DEFAULT '0',
  `disintegrating_ship` bigint(20) unsigned NOT NULL DEFAULT '0',
  `rocket_launcher` bigint(20) unsigned NOT NULL DEFAULT '0',
  `light_laser` bigint(20) unsigned NOT NULL DEFAULT '0',
  `heavy_laser` bigint(20) unsigned NOT NULL DEFAULT '0',
  `gauss_cannon` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ion_cannon` bigint(20) unsigned NOT NULL DEFAULT '0',
  `plasma_turret` bigint(20) unsigned NOT NULL DEFAULT '0',
  `small_shield_dome` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `large_shield_dome` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `antiballistic_missile` bigint(20) unsigned NOT NULL DEFAULT '0',
  `interplanetary_missile` bigint(20) unsigned NOT NULL DEFAULT '0',
  `metal_mine_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10',
  `crystal_mine_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10',
  `deuterium_synthesizer_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10',
  `solar_plant_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10',
  `fusion_reactor_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10',
  `solar_satellite_workpercent` tinyint(3) unsigned NOT NULL DEFAULT '10',
  `lunar_base` int(10) unsigned NOT NULL DEFAULT '0',
  `sensor_phalanx` int(10) unsigned NOT NULL DEFAULT '0',
  `jumpgate` int(10) unsigned NOT NULL DEFAULT '0',
  `last_jump_time` int(10) unsigned NOT NULL DEFAULT '0',
  `quantumgate` int(10) unsigned NOT NULL DEFAULT '0',
  `quantumgate_lastuse` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_owner` (`id_owner`),
  KEY `galaxy` (`galaxy`,`system`,`planet`),
  KEY `abandon_time` (`abandon_time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `polls`
--

CREATE TABLE IF NOT EXISTS `{prefix}_polls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `desc` text NOT NULL,
  `answers` text NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `open` tinyint(1) NOT NULL,
  `show_results` tinyint(1) NOT NULL,
  `obligatory` tinyint(1) NOT NULL,
  `Opt_Multivote` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `poll_votes`
--

CREATE TABLE IF NOT EXISTS `{prefix}_poll_votes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `poll_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `answer` text NOT NULL,
  `time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `poll_id` (`poll_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `premiumcodes`
--

CREATE TABLE IF NOT EXISTS `{prefix}_premiumcodes` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `Date` int(10) unsigned NOT NULL,
  `Type` tinyint(1) unsigned NOT NULL,
  `Code` varchar(8) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `premiumpayments`
--

CREATE TABLE IF NOT EXISTS `{prefix}_premiumpayments` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `Date` int(10) unsigned NOT NULL,
  `Item` tinyint(3) unsigned NOT NULL,
  `Free` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `premium_free`
--

CREATE TABLE IF NOT EXISTS `{prefix}_premium_free` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `GiveDate` int(10) unsigned NOT NULL,
  `UseDate` int(10) unsigned NOT NULL DEFAULT '0',
  `GivenBy` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ItemID` int(10) unsigned NOT NULL,
  `Used` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `records`
--

CREATE TABLE IF NOT EXISTS `{prefix}_records` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_owner` int(10) unsigned NOT NULL,
  `element` smallint(4) unsigned NOT NULL,
  `count` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `referring_table`
--

CREATE TABLE IF NOT EXISTS `{prefix}_referring_table` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `referrer_id` int(10) unsigned NOT NULL,
  `newuser_id` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `reg_ip` text NOT NULL,
  `matches_found` text,
  `provisions_granted` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `referrer_id` (`referrer_id`),
  KEY `newuser_id` (`newuser_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE IF NOT EXISTS `{prefix}_reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `date` int(10) unsigned NOT NULL,
  `sender_id` int(10) unsigned NOT NULL,
  `report_type` tinyint(1) unsigned NOT NULL,
  `report_element` bigint(20) unsigned NOT NULL,
  `report_user` int(10) unsigned NOT NULL,
  `user_info` text NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sim_battle_reports`
--

CREATE TABLE IF NOT EXISTS `{prefix}_sim_battle_reports` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `time` int(10) unsigned NOT NULL,
  `report` text NOT NULL,
  `owner` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `smart_fleet_blockade`
--

CREATE TABLE IF NOT EXISTS `{prefix}_smart_fleet_blockade` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `AdminID` int(10) unsigned NOT NULL,
  `Type` tinyint(1) unsigned NOT NULL,
  `BlockMissions` text NOT NULL,
  `Reason` text NOT NULL,
  `StartTime` int(10) unsigned NOT NULL,
  `EndTime` int(10) unsigned NOT NULL,
  `PostEndTime` int(10) unsigned NOT NULL,
  `ElementID` bigint(20) unsigned NOT NULL DEFAULT '0',
  `DontBlockIfIdle` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `statpoints`
--

CREATE TABLE IF NOT EXISTS `{prefix}_statpoints` (
  `id_owner` int(10) unsigned NOT NULL,
  `stat_type` tinyint(3) unsigned NOT NULL,
  `tech_rank` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_old_rank` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_yesterday_rank` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_points` bigint(20) unsigned NOT NULL DEFAULT '0',
  `tech_count` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_rank` int(10) unsigned NOT NULL DEFAULT '0',
  `build_old_rank` int(10) unsigned NOT NULL DEFAULT '0',
  `build_yesterday_rank` int(10) unsigned NOT NULL DEFAULT '0',
  `build_points` bigint(20) unsigned NOT NULL DEFAULT '0',
  `build_count` bigint(20) unsigned NOT NULL DEFAULT '0',
  `defs_rank` int(10) unsigned NOT NULL DEFAULT '0',
  `defs_old_rank` int(10) unsigned NOT NULL DEFAULT '0',
  `defs_yesterday_rank` int(10) unsigned NOT NULL DEFAULT '0',
  `defs_points` bigint(20) unsigned NOT NULL DEFAULT '0',
  `defs_count` bigint(20) unsigned NOT NULL DEFAULT '0',
  `fleet_rank` int(10) unsigned NOT NULL DEFAULT '0',
  `fleet_old_rank` int(10) unsigned NOT NULL DEFAULT '0',
  `fleet_yesterday_rank` int(10) unsigned NOT NULL DEFAULT '0',
  `fleet_points` bigint(20) unsigned NOT NULL DEFAULT '0',
  `fleet_count` bigint(20) unsigned NOT NULL DEFAULT '0',
  `total_rank` int(10) unsigned NOT NULL DEFAULT '0',
  `total_old_rank` int(10) unsigned NOT NULL DEFAULT '0',
  `total_yesterday_rank` int(10) unsigned NOT NULL DEFAULT '0',
  `total_points` bigint(20) unsigned NOT NULL DEFAULT '0',
  `total_count` bigint(20) unsigned NOT NULL DEFAULT '0',
  KEY `id_owner` (`id_owner`),
  KEY `stat_type` (`stat_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_alerts`
--

CREATE TABLE IF NOT EXISTS `{prefix}_system_alerts` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Sender` smallint(5) unsigned NOT NULL,
  `Date` int(10) unsigned NOT NULL,
  `Type` smallint(5) unsigned NOT NULL,
  `Code` smallint(5) unsigned NOT NULL,
  `Importance` tinyint(1) unsigned NOT NULL,
  `Status` tinyint(1) unsigned NOT NULL,
  `User_ID` int(10) unsigned NOT NULL,
  `Other_Data` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `system_alerts_filters`
--

CREATE TABLE IF NOT EXISTS `{prefix}_system_alerts_filters` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Date` int(10) unsigned NOT NULL,
  `Enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `ActionType` tinyint(1) unsigned NOT NULL,
  `SearchData` text NOT NULL,
  `HighCode` text NOT NULL,
  `EvalCode` text NOT NULL,
  `UseCount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `telemetry_data`
--

CREATE TABLE IF NOT EXISTS `{prefix}_telemetry_data` (
  `DataID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `PlaceID` smallint(5) unsigned NOT NULL,
  `UserID` int(10) unsigned NOT NULL,
  `TimeStamp` int(10) unsigned NOT NULL,
  `Data` text NOT NULL,
  PRIMARY KEY (`DataID`),
  KEY `PlaceID` (`PlaceID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `telemetry_pages`
--

CREATE TABLE IF NOT EXISTS `{prefix}_telemetry_pages` (
  `ID` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `Page` text NOT NULL,
  `Get` text NOT NULL,
  `Hash` char(32) NOT NULL,
  `HasPost` tinyint(1) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Hash` (`Hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `used_ip_and_ua`
--

CREATE TABLE IF NOT EXISTS `{prefix}_used_ip_and_ua` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Type` enum('ip','ua') NOT NULL,
  `Value` text NOT NULL,
  `ValueHash` varchar(32) NOT NULL,
  `SeenCount` int(10) unsigned NOT NULL DEFAULT '1',
  `isProxy` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ValueHash` (`ValueHash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `{prefix}_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `password` char(32) NOT NULL DEFAULT '',
  `email` varchar(64) NOT NULL DEFAULT '',
  `email_2` varchar(64) NOT NULL DEFAULT '',
  `lang` varchar(8) NOT NULL DEFAULT 'en',
  `authlevel` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `isAI` tinyint(1) NOT NULL DEFAULT '0',
  `avatar` varchar(255) NOT NULL DEFAULT '',
  `id_planet` bigint(20) unsigned NOT NULL DEFAULT '0',
  `galaxy` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `system` smallint(5) unsigned NOT NULL DEFAULT '0',
  `planet` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `current_planet` bigint(20) unsigned NOT NULL DEFAULT '0',
  `user_lastip` varchar(16) NOT NULL DEFAULT '',
  `ip_at_reg` varchar(16) NOT NULL DEFAULT '',
  `user_agent` text NOT NULL,
  `screen_settings` varchar(12) NOT NULL,
  `current_page` text NOT NULL,
  `register_time` int(10) unsigned NOT NULL DEFAULT '0',
  `onlinetime` int(10) unsigned NOT NULL DEFAULT '0',
  `first_login` int(10) unsigned NOT NULL DEFAULT '0',
  `NoobProtection_EndTime` int(10) unsigned NOT NULL DEFAULT '0',
  `skinpath` varchar(255) NOT NULL DEFAULT '',
  `design` tinyint(1) NOT NULL DEFAULT '1',
  `noipcheck` tinyint(1) NOT NULL DEFAULT '0',
  `planet_sort` tinyint(1) NOT NULL DEFAULT '0',
  `planet_sort_order` tinyint(1) NOT NULL DEFAULT '0',
  `planet_sort_moons` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `settings_spyprobescount` smallint(5) unsigned NOT NULL DEFAULT '1',
  `settings_esp` tinyint(4) NOT NULL DEFAULT '1',
  `settings_wri` tinyint(4) NOT NULL DEFAULT '1',
  `settings_bud` tinyint(4) NOT NULL DEFAULT '1',
  `settings_mis` tinyint(4) NOT NULL DEFAULT '1',
  `settings_useprettyinputbox` tinyint(1) NOT NULL DEFAULT '1',
  `settings_resSortArray` char(11) NOT NULL DEFAULT 'met,cry,deu',
  `settings_mainPlanetID` bigint(20) unsigned NOT NULL DEFAULT '0',
  `settings_msgperpage` smallint(4) unsigned NOT NULL DEFAULT '20',
  `settings_spyexpand` tinyint(1) NOT NULL DEFAULT '0',
  `settings_DevelopmentOld` tinyint(1) NOT NULL DEFAULT '0',
  `settings_ExpandedBuildView` tinyint(1) NOT NULL DEFAULT '1',
  `settings_UseAJAXGalaxy` tinyint(1) NOT NULL DEFAULT '1',
  `settings_UseMsgThreads` tinyint(1) NOT NULL DEFAULT '1',
  `settings_FleetColors` text NOT NULL,
  `settings_Galaxy_ShowUserAvatars` tinyint(1) NOT NULL DEFAULT '0',
  `is_banned` tinyint(1) NOT NULL DEFAULT '0',
  `is_onvacation` tinyint(1) NOT NULL DEFAULT '0',
  `is_ondeletion` tinyint(1) NOT NULL DEFAULT '0',
  `ban_endtime` int(10) unsigned NOT NULL DEFAULT '0',
  `vacation_starttime` int(10) unsigned NOT NULL DEFAULT '0',
  `vacation_endtime` int(10) unsigned NOT NULL DEFAULT '0',
  `vacation_leavetime` int(10) unsigned NOT NULL DEFAULT '0',
  `vacation_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - Normal; 1 - Ban; 2 - Admin',
  `deletion_endtime` int(10) unsigned NOT NULL DEFAULT '0',
  `techQueue_Planet` bigint(20) unsigned NOT NULL DEFAULT '0',
  `techQueue_EndTime` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_espionage` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_computer` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_weapons` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_armour` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_shielding` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_energy` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_hyperspace` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_combustiondrive` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_impulsedrive` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_hyperspacedrive` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_laser` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_ion` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_plasma` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_interresearchnetwork` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_expedition` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_antimatter` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_disintegration` int(10) unsigned NOT NULL DEFAULT '0',
  `tech_graviton` int(10) unsigned NOT NULL DEFAULT '0',
  `ally_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ally_request` int(10) unsigned NOT NULL DEFAULT '0',
  `ally_request_text` text,
  `ally_register_time` int(10) unsigned NOT NULL DEFAULT '0',
  `ally_rank_id` int(10) unsigned NOT NULL DEFAULT '0',
  `multi_validated` tinyint(1) NOT NULL DEFAULT '0',
  `multiIP_DeclarationID` bigint(20) unsigned NOT NULL DEFAULT '0',
  `darkEnergy` int(10) unsigned NOT NULL DEFAULT '0',
  `block_cookies` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `dokick` tinyint(1) NOT NULL DEFAULT '0',
  `activation_code` varchar(32) NOT NULL DEFAULT '',
  `new_pass` varchar(32) NOT NULL DEFAULT '',
  `new_pass_code` varchar(32) NOT NULL DEFAULT '',
  `last_send_activationcode` int(10) unsigned NOT NULL DEFAULT '0',
  `last_send_newpass` int(10) unsigned NOT NULL DEFAULT '0',
  `pro_time` int(10) unsigned NOT NULL DEFAULT '0',
  `trader_usesCount` int(10) unsigned NOT NULL DEFAULT '0',
  `spy_jam_time` int(10) unsigned NOT NULL DEFAULT '0',
  `geologist_time` int(10) unsigned NOT NULL DEFAULT '0',
  `engineer_time` int(10) unsigned NOT NULL DEFAULT '0',
  `admiral_time` int(10) unsigned NOT NULL DEFAULT '0',
  `technocrat_time` int(10) unsigned NOT NULL DEFAULT '0',
  `referred` int(10) unsigned NOT NULL DEFAULT '0',
  `additional_planets` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `old_username` varchar(64) NOT NULL DEFAULT '',
  `old_username_expire` int(10) unsigned NOT NULL DEFAULT '0',
  `chat_GhostMode` tinyint(1) NOT NULL DEFAULT '0',
  `chat_GhostMode_DontCount` tinyint(1) NOT NULL DEFAULT '0',
  `tasks_done` text,
  `achievements_unlocked` text,
  `rules_accept_stamp` int(10) unsigned NOT NULL DEFAULT '0',
  `morale_level` tinyint(4) NOT NULL DEFAULT '0',
  `morale_points` bigint(20) unsigned NOT NULL DEFAULT '0',
  `morale_droptime` int(10) unsigned NOT NULL DEFAULT '0',
  `morale_lastupdate` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ally_id` (`ally_id`),
  KEY `ally_request` (`ally_request`),
  KEY `onlinetime` (`onlinetime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_developmentdumps`
--

CREATE TABLE IF NOT EXISTS `{prefix}_user_developmentdumps` (
  `UserID` int(10) unsigned NOT NULL,
  `Date` int(10) unsigned NOT NULL,
  `Planets` text NOT NULL,
  `Techs` text NOT NULL,
  `InFlight` text NOT NULL,
  PRIMARY KEY (`UserID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_developmentlog`
--

CREATE TABLE IF NOT EXISTS `{prefix}_user_developmentlog` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `Date` int(10) unsigned NOT NULL,
  `Place` tinyint(2) unsigned NOT NULL,
  `PlanetID` bigint(20) unsigned NOT NULL,
  `Code` tinyint(3) unsigned NOT NULL,
  `ElementID` bigint(20) unsigned NOT NULL,
  `AdditionalData` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_enterlog`
--

CREATE TABLE IF NOT EXISTS `{prefix}_user_enterlog` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `User_ID` int(10) unsigned NOT NULL,
  `IP_ID` int(10) unsigned NOT NULL,
  `UA_ID` int(10) unsigned NOT NULL,
  `Times` text NOT NULL,
  `Count` int(10) unsigned NOT NULL DEFAULT '1',
  `FailCount` int(10) unsigned NOT NULL DEFAULT '0',
  `LastTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `User_ID` (`User_ID`),
  KEY `IP_ID` (`IP_ID`),
  KEY `UA_ID` (`UA_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Query creating config rows
--

INSERT INTO `{prefix}_config` (`config_name`, `config_value`) VALUES
('game_name', '{Config_GameName}'),
('users_amount', '1'),
('game_speed', '{Config_GameSpeed}'),
('fleet_speed', '{Config_FleetSpeed}'),
('resource_multiplier', '{Config_ResourceSpeed}'),
('Fleet_Cdr', '{Config_FleetDebris}'),
('Defs_Cdr', '{Config_DefenseDebris}'),
('Debris_Def_Rocket', '{Config_MissileDebris}'),
('initial_fields', '{Config_InitialFields}'),
('COOKIE_NAME', '{Config_CookieName}'),
('game_disable', '0'),
('close_reason', ''),
('rekord', '1'),
('metal_basic_income', '20'),
('crystal_basic_income', '10'),
('deuterium_basic_income', '0'),
('energy_basic_income', '0'),
('last_update', UNIX_TIMESTAMP()),
('BuildLabWhileRun', '0'),
('noobprotection', '{Config_NoobProtection_Enable}'),
('noobprotectiontime', '{Config_NoobProtection_BasicLimit_Time}'),
('noobprotectionmulti', '{Config_NoobProtection_BasicLimit_Multiplier}'),
('no_noob_protect', '{Config_NoobProtection_ProtectionRemove}'),
('no_idle_protect', '{Config_NoobProtection_IdleDaysProtection}'),
('OverviewNewsFrame', '0'),
('OverviewNewsText', ''),
('OverviewBanner', '0'),
('OverviewClickBanner', ''),
('stat_settings', '1000'),
('enable_bbcode', '1'),
('adminprotection', '1'),
('allyprotection', '0'),
('banned_ip_list', ''),
('last_db_optimization', UNIX_TIMESTAMP()),
('last_stats_daily', UNIX_TIMESTAMP()),
('BannedMailDomains', ''),
('Protection_NewPlayerTime', '{Config_NoobProtection_FirstLoginProtection}'),
('Protection_AntiFarmEnabled', '{Config_AntiFarm_Enable}'),
('Protection_AntiFarmRate', '{Config_AntiFarm_UserStatsRate}'),
('Protection_AntiFarmCountTotal', '{Config_AntiFarm_CountTotal}'),
('Protection_AntiFarmCountPlanet', '{Config_AntiFarm_CountPlanet}'),
('Protection_BashLimitEnabled', '{Config_BashLimit_Enabled}'),
('Protection_BashLimitInterval', '{Config_BashLimit_Interval}'),
('Protection_BashLimitCountTotal', '{Config_BashLimit_CountTotal}'),
('Protection_BashLimitCountPlanet', '{Config_BashLimit_CountPlanet}'),
('TelemetryEnabled', '{Config_TelemetryEnabled}'),
('enforceRulesAcceptance', '0'),
('last_rules_changes', '0'),
('EngineInfo_Version', '1.0.0'),
('EngineInfo_BuildNo', '1'),
('cron_GC_LastOptimize', UNIX_TIMESTAMP());

-- --------------------------------------------------------

--
-- Query - create Admin User
--

INSERT INTO `{prefix}_users` SET
`id` = 1,
`username` = '{AdminUser_name}',
`password` = '{AdminUser_passhash}',
`email` = '{AdminUser_email}',
`email_2` = '{AdminUser_email}',
`lang` = 'en',
`authlevel` = 120,
`id_planet` = 1,
`galaxy` = 1,
`system` = 1,
`planet` = 1,
`current_planet` = 1,
`register_time` = UNIX_TIMESTAMP(),
`onlinetime` = UNIX_TIMESTAMP(),
`first_login` = UNIX_TIMESTAMP(),
`NoobProtection_EndTime` = UNIX_TIMESTAMP(),
`skinpath` = 'skins/epicblue/',
`design` = 1,
`settings_mainPlanetID` = 1;

-- --------------------------------------------------------

--
-- Query - create Admin Planet
--

INSERT INTO `{prefix}_planets` SET
`id` = 1,
`name` = 'Admin Planet',
`id_owner` = 1,
`galaxy` = 1,
`system` = 1,
`planet` = 1,
`last_update` = UNIX_TIMESTAMP(),
`planet_type` = 1;

-- --------------------------------------------------------

--
-- Query - create Admin GalaxyRow
--

INSERT INTO `{prefix}_galaxy` SET
`galaxy_id` = 1,
`galaxy` = 1,
`system` = 1,
`planet` = 1,
`id_planet` = 1;
