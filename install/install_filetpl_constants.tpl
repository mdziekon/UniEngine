<?php

if(!defined('INSIDE'))
{
    die('Access Denied');
}

define('UNIENGINE_UNIID'                            , '{UNIID}');

define('LOGINPAGE_UNIVERSUMCODE'                    , 'uni1');
define('LOGINPAGE_ALLOW_LOGINPHP'                   , true);

// Main data - adminMail, gameURL
define('ADMINEMAIL'                                 , '{AdminEmail}');
define('GAMEURL'                                    , 'http://'.$_SERVER['HTTP_HOST'].'/');
define('GAMEURL_DOMAIN'                             , '{Domain}');
define('GAMEURL_STRICT'                             , 'http://{Domain}');
define('GAMEURL_UNISTRICT'                          , 'http://{GenerateSubdomainLink}');
define('UNI_DEFAULT_LANG'                           , '{UniDefaultLang}');

// Am I playing or Beta? TRUE/FALSE (If TRUE - Everyone has ProAccount)
define('BETA'                                       , FALSE);
define('UNIENGINE_HASAPC'                           , function_exists('apc_fetch'));

// Automation Tools
define('AUTOTOOL_ZIPLOGS_PASSWORDHASH'              , '{AutoTool_ZipLog_Hash}');
define('AUTOTOOL_STATBUILDER_PASSWORDHASH'          , '{AutoTool_StatBuilder_Hash}');
define('AUTOTOOL_GARBAGECOLLECTOR_PASSWORDHASH'     , '{AutoTool_GC_Hash}');

// Mailer Settings
define('MAILER_SMTP_USE'                            , false);
define('MAILER_SMTP_HOST'                           , 'IPADDRESS');
define('MAILER_SMTP_PORT'                           , 0000);
define('MAILER_SMTP_USER'                           , 'SMTPUSER');
define('MAILER_SMTP_PASSWORD'                       , 'SMTPPASSWORD');

define('MAILER_MSGFIELDS_FROM'                      , 'noreply@{Domain}');
define('MAILER_MSGFIELDS_FROM_NAME'                 , '{GameName}');

// --- Morale System ---
define('MORALE_ENABLED'                             , false);
define('MORALE_MINIMALFACTOR'                       , 5);
define('MORALE_MAXIMALFACTOR'                       , 50);
define('MORALE_MAXDROPTIME_POSITIVE'                , (3 * 24 * 60 * 60));
define('MORALE_MAXDROPTIME_NEGATIVE'                , (7 * 24 * 60 * 60));
define('MORALE_ROCKETATTACK_MODIFIER'               , 1/4);
define('MORALE_DROPINTERVAL_POSITIVE'               , (30 * 60));
define('MORALE_DROPINTERVAL_NEGATIVE'               , (60 * 60));

define('MORALE_POSITIVE'                            , 1);
define('MORALE_NEGATIVE'                            , -1);

// Morale Bonuses and Penalties
define('MORALE_BONUS_SOLOIDLERSTEAL'                , 85);
define('MORALE_BONUS_SOLOIDLERSTEAL_STEALPERCENT'   , 75);
define('MORALE_BONUS_FLEETSPEEDUP1'                 , 10);
define('MORALE_BONUS_FLEETSPEEDUP1_VALUE'           , 10);
define('MORALE_BONUS_FLEETSPEEDUP2'                 , 25);
define('MORALE_BONUS_FLEETSPEEDUP2_VALUE'           , 20);
define('MORALE_BONUS_FLEETPOWERUP1'                 , 50);
define('MORALE_BONUS_FLEETPOWERUP1_FACTOR'          , 1.2);
define('MORALE_BONUS_FLEETSHIELDUP1'                , 70);
define('MORALE_BONUS_FLEETSHIELDUP1_FACTOR'         , 1.2);
define('MORALE_BONUS_FLEETSDADDITION'               , 100);
define('MORALE_BONUS_FLEETSDADDITION_VALUE'         , 1);

define('MORALE_PENALTY_EMPTYSPYREPORT'              , -35);
define('MORALE_PENALTY_EMPTYSPYREPORT_CHANCE'       , 50);
define('MORALE_PENALTY_FLEETSLOWDOWN'               , -10);
define('MORALE_PENALTY_FLEETSLOWDOWN_VALUE'         , 0.85);
define('MORALE_PENALTY_IDLERSTEAL'                  , -60);
define('MORALE_PENALTY_IDLERSTEAL_STEALPERCENT'     , 25);
define('MORALE_PENALTY_STEAL'                       , -80);
define('MORALE_PENALTY_STEAL_STEALPERCENT'          , 25);
define('MORALE_PENALTY_RESOURCELOSE'                , -60);
define('MORALE_PENALTY_RESOURCELOSE_STEALPERCENT'   , 75);
define('MORALE_PENALTY_FLEETSHIELDDOWN1'            , -20);
define('MORALE_PENALTY_FLEETSHIELDDOWN1_FACTOR'     , 0.85);
define('MORALE_PENALTY_FLEETSHIELDDOWN2'            , -50);
define('MORALE_PENALTY_FLEETSHIELDDOWN2_FACTOR'     , 0.7);
define('MORALE_PENALTY_FLEETPOWERDOWN1'             , -20);
define('MORALE_PENALTY_FLEETPOWERDOWN1_FACTOR'      , 0.85);
define('MORALE_PENALTY_FLEETPOWERDOWN2'             , -50);
define('MORALE_PENALTY_FLEETPOWERDOWN2_FACTOR'      , 0.75);
define('MORALE_PENALTY_FLEETSDDOWN'                 , -80);
define('MORALE_PENALTY_FLEETSDDOWN_FACTOR'          , 0.5);

// --- AuthLevels ---
define('AUTHLEVEL_GAMEOWNER'                        , 120);
define('AUTHLEVEL_PROGRAMMER'                       , 110);
define('AUTHLEVEL_MAINADMIN'                        , 100);
define('AUTHLEVEL_SUPPORTADMIN'                     , 90);
define('AUTHLEVEL_SUPERGAMEOPERATOR'                , 70);
define('AUTHLEVEL_GAMEOPERATOR'                     , 50);
define('AUTHLEVEL_FORUMTEAM'                        , 20);
define('AUTHLEVEL_USER'                             , 0);

define('AUTHCHECK_NORMAL'                           , 1);
define('AUTHCHECK_HIGHER'                           , 2);
define('AUTHCHECK_EXACT'                            , 3);

// --- Login Protection Settings ---
// How many times User can try to login?
define('LOGINPROTECTION_MAXATTEMPTS'                , 5);
// How much time User has to wait till login lock will wear off? [Seconds]
define('LOGINPROTECTION_LOCKTIME'                   , 300); // 5min

// --- Bash Settings ---
define('BASH_PERUSER'                               , 15);
define('BASH_PERPLANET'                             , 5);

// Definition of Known Universe
define('MAX_GALAXY_IN_WORLD'                        , 9);
define('MAX_SYSTEM_IN_GALAXY'                       , 499);
define('MAX_PLANET_IN_SYSTEM'                       , 15);

// Number of columns in SpyReport
define('SPY_REPORT_ROW'                             , 2);

// --- Vacation Mode Settings ---
// Minimal length of VacationMode (in Free mode) [Seconds]
define('MINURLOP_FREE'                              , 259200);
// Minimal length of VacationMode (in Pro mode) [Seconds]
define('MINURLOP_PRO'                               , 259200);
// Maximal length of VacationMode (to prevent "Infinite VacationMode") [Days]
define('MAXVACATIONS_REG'                           , 30);
// --- Deletion Mode Settings ---
// How many time has to pass to delete User's Account [Days]
define('ACCOUNT_DELETION_TIME'                      , 7);

// --- Account Activation Settings ---
// How much Time user has, till his Account will be blocked, if he won't activate it [Seconds]
define('NONACTIVE_PLAYTIME'                         , 43200); // 12h
// How much Time user has, till his Account will be deleted, if he won't activate it [Seconds]
define('NONACTIVE_DELETETIME'                       , 604800); // 7d

// --- Planets & User Settings ---
// How many fields Moonbasis is giving
define('FIELDS_BY_MOONBASIS_LEVEL'                  , 4);
define('FIELDS_ADDED_BY_TERRAFORMER'                , 5);
// How much time must pass, untill Planet will be deleted? (0 means instant deletion on Abandon) [Seconds]
define('PLANET_ABANDONTIME'                         , 0);
// Maximal number of planets per user
define('MAX_PLAYER_PLANETS'                         , 10);

// QueueLength - StructuresQueue
define('MAX_BUILDING_QUEUE_SIZE'                    , 3);
define('MAX_BUILDING_QUEUE_SIZE_PRO'                , 10);
// QueueLength - ShipyardQueue
define('MAX_FLEET_OR_DEFS_PER_ROW'                  , 1000000);
define('MAX_FLEET_OR_DEFS_PER_ROW_PRO'              , 1000000);
// QueueLength - TechnologyQueue
define('MAX_TECH_QUEUE_LENGTH'                      , 1);
define('MAX_TECH_QUEUE_LENGTH_PRO'                  , 5);

// Maximal Overflow of Storages (1.0 = 100%)
define('MAX_OVERFLOW'                               , 1.0);
// How many resources return in Disassembler?
define('DISASSEMBLER_PERCENT'                       , 0.7);
// Do you want to show Admin in Records and Stats? (1 - Yes / 0 - No)
define('SHOW_ADMIN_IN_RECORDS'                      , 0);

// --- Ally Settings ---
// AllyPacts Types
define('ALLYPACT_NONAGGRESSION'                     , 1);
define('ALLYPACT_MERCANTILE'                        , 2);
define('ALLYPACT_DEFENSIVE'                         , 3);
define('ALLYPACT_MILITARY'                          , 4);

// Base mining values and base storage size
define('BASE_STORAGE_SIZE'                          , 100000);
define('BUILD_METAL'                                , 1000);
define('BUILD_CRISTAL'                              , 1000);
define('BUILD_DEUTERIUM'                            , 1000);
define('MAX_REFUND_VALUE'                           , 10000000);

// BlockingCookie name
define('COOKIE_BLOCK'                               , 'UMB15HA87Y4M');
define('COOKIE_BLOCK_VAL'                           , '58847718139');

// Registry check Cookie
define('REGISTER_REQUIRE_EMAILCONFIRM'              , {Reg_RequireEmailConfirm});
define('REGISTER_RECAPTCHA_ENABLE'                  , {Reg_RecaptchaEnabled});
define('REGISTER_RECAPTCHA_SERVERIP_AS_HOSTNAME'    , {Reg_Recaptcha_ServerIP_As_Hostname});
define('REGISTER_RECAPTCHA_PRIVATEKEY'              , '{Reg_Recaptcha_Private}');
define('REGISTER_RECAPTCHA_PUBLICKEY'               , '{Reg_Recaptcha_Public}');
define('REGISTER_CHECK'                             , 'VBF0BU794ETH1');

// Reffering System Setup
// ------------------------------------------------
define('REFERING_COOKIENAME'                        , 'YN3F53VBAS13M');
define('REFERING_PROVISION'                         , 0.2);

// --- CombatSystem Constants ---
define('COMBAT_DRAW'                                , 0);
define('COMBAT_ATK'                                 , 1);
define('COMBAT_DEF'                                 ,-1);
define('MAX_ACS_JOINED_PLAYERS'                     , 4);
define('ACS_MAX_JOINED_FLEETS'                      , 15);
define('ACS_MINIMALFORCECONTRIBUTION'               , 0.05);
define('COMBAT_MOONPERCENT_RESOURCES'               , 100000);
// Maximal number of rounds in battle
define('BATTLE_MAX_ROUNDS'                          , 7);
define('COMBAT_RESOURCESTEAL_PERCENT'               , 50);

// Constants required for Time Calculations
define('SERVER_MAINOPEN_TSTAMP'                     , {InsertServerMainOpenTime});

define('TIME_ONLINE'                                , 900);
define('TIME_HOUR'                                  , 3600);
define('TIME_DAY'                                   , 86400);
define('TIME_YEAR'                                  , 31536000);

// --- InGame Constants ---
define('QUANTUMGATE_INTERVAL_HOURS'                 , 6);
define('PHALANX_DEUTERIUMCOST'                      , 5000);
define('SILO_PERLEVELPLACE'                         , 25);

// RegExp Definitions
define('REGEXP_POLISHSIGNS'                         , 'ążśźęćńółĄŻŚŹĘĆŃÓŁ');
define('REGEXP_USERNAME'                            , '/^[a-zA-Z'.REGEXP_POLISHSIGNS.'0-9\_\-\.\ ]{1,64}$/D');
define('REGEXP_USERNAME_ABSOLUTE'                   , '/^[a-zA-Z'.REGEXP_POLISHSIGNS.'0-9\_\-\.\ ]{4,64}$/D');
define('REGEXP_IP'                                  , '/^[0-9a-fA-F\.\:]{1,39}$/D');
define('REGEXP_ALLYNAMEANDTAG'                      , '/^[0-9a-zA-Z\ \-\_'.REGEXP_POLISHSIGNS.']{1,35}$/D');
define('REGEXP_ALLYTAG'                             , '/^[0-9a-zA-Z\ \-\_'.REGEXP_POLISHSIGNS.']{1,8}$/D');
define('REGEXP_ALLYNAME_ABSOLUTE'                   , '/^[0-9a-zA-Z\ \-\_'.REGEXP_POLISHSIGNS.']{1,35}$/D');
define('REGEXP_ALLYTAG_ABSOLUTE'                    , '/^[0-9a-zA-Z\ \-\_'.REGEXP_POLISHSIGNS.']{3,8}$/D');
define('REGEXP_PLANETNAME_ABSOLUTE'                 , '/^[0-9a-zA-Z\ \-\_'.REGEXP_POLISHSIGNS.']{1,20}$/D');
define('REGEXP_SANITIZELIKE_SEARCH'                 , '#(\%|\_){1}#si');
define('REGEXP_SANITIZELIKE_REPLACE'                , '\\\$1');
define('REGEXP_EMAIL_STRICT'                        , '/^[^\W][a-zA-Z0-9_\-]+(\.[a-zA-Z0-9_\-]+)*\@[a-zA-Z0-9_\-]+(\.[a-zA-Z0-9_\-]+)*\.[a-zA-Z]{2,4}$/');
define('REGEXP_EMAIL_SIGNS'                         , '/^[a-zA-Z0-9\.\_\@\-\+]$/');
define('REGEXP_HEXCOLOR'                            , '/^#[0-9A-Za-z]{0,6}$/');

?>
