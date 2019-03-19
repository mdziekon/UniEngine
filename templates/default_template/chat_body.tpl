<link rel="stylesheet" type="text/css" href="dist/css/chat.cachebuster-1546565145290.min.css" />
<script type="text/javascript" src="dist/js/chat.cachebuster-1552960387022.min.js"></script>
<script>
var YourNickname = '{Online_You_Name}';
var JSLang =
{
    'usrRef': '{jsLang_usrRef}',
    'profile': '{jsLang_profile}',
    'mentioned': '{jsLang_mentioned}',
    'ToolTip_ReportMsg': '{jsLang_ToolTip_ReportMsg}'
};
var ErrorMsg =
{
    'getError': '{jQueryAjax_getError}',
    'getErrorFatal': '{jQueryAjax_getErrorFatal}',
    'jQueryAjax_postError': '{jQueryAjax_postError}',
    'jQueryAjax_postErr_2': '{jQueryAjax_postErr_2}',
    'jQueryAjax_postErr_3': '{jQueryAjax_postErr_3}',
    'jQueryAjax_delErr_2': '{jQueryAjax_delErr_2}',
    'jQueryAjax_delErr_3': '{jQueryAjax_delErr_3}',
    'jQueryAjax_delErr_4': '{jQueryAjax_delErr_4}',
    'jQueryAjax_notLogged': '{jQueryAjax_notLogged}'
};
var JSLang_Errors = {Insert_JSLang_Errors};

var LastSeenID = {LastSeenID};
var ServerStamp = {ServerStamp};
var UserAuth = {UserAuth};
var RoomID = {RoomID};
</script>
<br/>
<table style="width: 900px;">
    <tr>
        <td class="c center" colspan="3"><b>{chat_disc}</b></td>
    </tr>
    <tr>
        <th colspan="3" style="padding: 0px;">
            <div id="shoutbox">
                <div class="upperBox hide" id="errorBox"></div>
                <div class="upperBox" id="onlineBox">{UsersOnline} (<span id="onlineCount">1</span>):
                    <a href="#" id="onlineBox_you" class="usrRef usrColor_{Online_You_Color} {Online_You_Invisible}" title="{jsLang_usrRef}">{Online_You_Name}</a> <a href="profile.php?uid={Online_You_ID}" class="usrColor_{Online_You_Color}" target="_blank" title="{jsLang_profile}">#</a><span id="onlineUsers"></span>
                </div>
                <div class="upperBox hide" id="activityBox">&nbsp;<img src="./images/ajax-loader.gif"/></div>
                <table cellspacing="0" id="sbTable"></table>
            </div>
        </th>
    </tr>
    <tr>
        <th style="width: 20%;">{chat_message}</th>
        <th style="width: 63%;"><textarea id="msgText" class="pad2"></textarea></th>
        <th style="width: 15%;"><input type="button" value="{chat_send}" id="msgSend"/></th>
    </tr>
</table>
<table style="width: 900px; margin-top: 5px;">
    <tr>
        <td class="c center" rowspan="3">{Legend_BBCode}</td>
        <th class="aright fNorm" style="width: 10%;"><b>{Legend_BBCode_B}</b></th>
        <th class="aleft fNorm" style="width: 25%">[b]{Legend_BBCode_Text}[/b]</th>
        <th class="aright fNorm" style="width: 10%;"><span style="text-decoration: line-through">{Legend_BBCode_S}</span></th>
        <th class="aleft fNorm" style="width: 40%">[s]{Legend_BBCode_Text}[/s]</th>
    </tr>
    <tr>
        <th class="aright fNorm"><i>{Legend_BBCode_I}</i></th>
        <th class="aleft fNorm">[i]{Legend_BBCode_Text}[/i]</th>
        <th class="aright fNorm"><a href="#"><img align="absmiddle" src="images/url.png"/> {Legend_BBCode_URL}</a></th>
        <th class="aleft fNorm">[url={Legend_BBCode_Adress}]{Legend_BBCode_Text}[/url] {Legend_BBCode_OR} [url]{Legend_BBCode_Adress}[/url]</th>
    </tr>
    <tr>
        <th class="aright fNorm"><u>{Legend_BBCode_U}</u></th>
        <th class="aleft fNorm">[u]{Legend_BBCode_Text}[/u]</th>
        <th class="aright fNorm"><span style="color: red;">{Legend_BBCode_C}</span></th>
        <th class="aleft fNorm">[color={Legend_BBCode_Color}]{Legend_BBCode_Text}[/color] <b>({Legend_BBCode_ColorInfo})</b></th>
    </tr>
    {Insert_Settings}
</table>
