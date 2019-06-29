<html lang="{PHP_CurrentLangISOCode}">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <title>{title}</title>
        <link rel="shortcut icon" href="{AdminBack}favicon.ico" type="image/x-icon" />
        <link rel="icon" href="{AdminBack}favicon.ico" type="image/x-icon" />
        <link rel="stylesheet" type="text/css" href="{SkinPath}default.css" />
        <link rel="stylesheet" type="text/css" href="{SkinPath}formate.css" />
        <meta name="description" content="GAMEDESCRIPTION" />
        <meta name="keywords" content="KEYWORDS" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        {PHP_Meta}
        <script type="text/javascript" src="{AdminBack}libs/overlib/overlib.min.js"></script>
        <script type="text/javascript" src="{AdminBack}libs/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="{AdminBack}libs/jquery-tipTip/jquery.tipTip.min.js"></script>
        <script type="text/javascript" src="{AdminBack}libs/jquery-qtip/jquery.qtip.pack.js"></script>
        <script type="text/javascript" src="{AdminBack}dist/js/main.normal.cachebuster-1545956361123.min.js"></script>
        <link rel="stylesheet" type="text/css" href="{AdminBack}dist/css/global.cachebuster-1546565145290.min.css" />
        <link rel="stylesheet" type="text/css" href="{AdminBack}libs/jquery-tipTip/jquery.tipTip.min.css" />
        <link rel="stylesheet" type="text/css" href="{AdminBack}libs/jquery-qtip/jquery.qtip.min.css" />
        <script>var PHPVar = {ServerTimeTxt: '{ServerTimeTxt}', ServerTimestamp: {ServerTimestamp}};</script>
    </head>
    <body>
        {PHP_InjectAfterBody}
        {TaskInfoBar}
        <div id="topMenu" class="pf t0 l0 w1 z1 center bc tmenu">
            <div class="dil pabs l0 ml">
                <b>
                    <a href="{AdminBack}profile.php?uid={UserID}">{Username}</a>
                    <a class="{AccType_Color}" style="margin-left: 5px;" href="{AdminBack}aboutpro.php" title="{AccType_Title}">({AccType_Name})</a>
                </b>
                {AdminLink}
            </div>
            <div class="dil">
                <span class="m6">
                    <a href="{AdminBack}chat.php" title="{ChatTitle}">{chat}</a> ({InsertChatMsgCount})
                </span>
                <span class="m6">
                    <a href="{AdminBack}buddy.php" title="{FriendsTitle}">{friends}</a>{InsertBuddyCount}
                </span>
                <a class="m6" href="{AdminBack}notes.php" title="{NotesTitle}">{notes}</a>
                <span class="m6">
                    <a href="{AdminBack}stats.php?range={userpoints}" title="{StatsTitle}">{stats}</a> ({userpoints})
                </span>
                <a class="m6" href="{AdminBack}settings.php" title="{OptionsTitle}">{options}</a>
                <a class="m6 red" href="{AdminBack}logout.php" title="{LogoutTitle}">{logout}</a>
            </div>
            <div id="clockDiv" class="dil pabs r0 mr" style="cursor: help;">
                <span id="clock">{now}</span>
            </div>
        </div>
        <div class="pr t0 l0 w1 inv pad2">&nbsp;</div>
        <div class="pr l0">
            <div class="pf l0 fl w2 z1 style">{left_menu_replace}</div>
            <div class="pr l0 w2 fl inv">&nbsp;</div>
            <div id="gameContent" class="fl w8">
                <center>{game_content_replace}</center>
            </div>
        </div>
        <div class="pf b0 l0 w1 z1 center bc bmenu">
            <div class="dil pabs l0 ml">
                <a class="m6" href="{AdminBack}banned.php" title="{BannedTitle}">{banned}</a> |
                <a class="m6 red" href="{AdminBack}report.php" title="{ReportTitle}">{ReportLink}</a> |
                <a class="m6 orange" href="{AdminBack}polls.php" title="{PollsTitle}">{Polls}</a>
            </div>
            {PHP_InjectIntoBottomMenu}
            <div class="dil inv">&nbsp;</div>
            <div class="dil pabs r0 mr">
                <a class="m6 lime" href="http://forum" title="{ForumTitle}" target="_blank">{forum}</a> |
                <a class="m6" href="{AdminBack}rules.php" title="{RulesTitle}" target="_blank">{rules}</a> |
                <a class="m6" href="{AdminBack}contact.php" title="{ContactTitle}">{contact}</a> |
                <a class="m6 skyblue" href="https://github.com/mdziekon/UniEngine" target="_blank">Powered by UniEngine</a>
            </div>
        </div>
        <div class="pr t0 l0 w1 inv pad2">&nbsp;</div>
    </body>
</html>
