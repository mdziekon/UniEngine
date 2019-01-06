<script>
var RecaptchaOptions =
{
    theme : 'clean',
    tabindex: 7,
    custom_translations:
    {
        instructions_visual : '{ReCaptcha_instructions_visual}',
        instructions_audio : "{ReCaptcha_instructions_audio}",
        play_again : "{ReCaptcha_play_again}",
        cant_hear_this : "{ReCaptcha_cant_hear_this}",
        visual_challenge : "{ReCaptcha_visual_challenge}",
        audio_challenge : "{ReCaptcha_audio_challenge}",
        refresh_btn : "{ReCaptcha_refresh_btn}",
        help_btn : "{ReCaptcha_help_btn}",
        incorrect_try_again : "{ReCaptcha_incorrect_try_again}"
    }
};
var JSLang = {Insert_JSLang};
</script>
<script type="text/javascript" src="libs/jquery-cookie/jquery.cookie-1.0.0.1.min.js"></script>
<script src="dist/js/register.cachebuster-1545956361123.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="dist/css/register.cachebuster-1546564327123.min.css" />
<br/>
<div>
    <h2 class="TextShadow">
        <font size="+3">{registry}</font>
        <br/>
        <a href="{GameURL}">{GameName}</a>
    </h2>
    <form id="form" action="" method="post">
        <input type="hidden" name="register" value="1"/>
        <table>
            <tbody>
                <tr id="MsgSpace">
                    <th id="MsgBox" class="pad1 TextShadow" colspan="2" style="height: 80px;">&nbsp;</th>
                </tr>
                <tr style="visibility: hidden;">
                    <th></th>
                </tr>
                <tr>
                    <th class="pad2 w350px"><label for="uniSel">{Universe}</label></th>
                    <th class="pad2 w350px">
                        <input id="prevUni" type="button" value="&#171;" class="pad2 fntBold w40px"/>
                        <select class="pad2 wid" id="uniSel" tabindex="1">
                            {Insert_UniSelectors}
                        </select>
                        <input id="nextUni" type="button" value="&#187;" class="pad2 fntBold w40px"/>
                    </th>
                </tr>
                <tr>
                    <th id="UniInfo" style="padding: 10px;" class="lal" colspan="2">
                        <div style="width: 700px; overflow: hidden;">
                            <div id="UniInfo_Holder" style="position: relative; width: 10000px; left: {Insert_UniInfo_Holder_LeftPos}px;">
                                {Insert_UniInfo_Boxes}
                            </div>
                        </div>
                    </th>
                </tr>
                <tr>
                    <th class="pad2">
                        <label for="username">{Input_Username}</label>
                    </th>
                    <th class="pad2">
                        <input class="pad2 wid" id="username" name="username" size="20" maxlength="64" type="text" tabindex="2" value=""/>
                    </th>
                </tr>
                <tr>
                    <th class="pad2">
                        <label for="password">{Input_Passoword}</label>
                    </th>
                    <th class="pad2">
                        <input class="pad2 wid" id="password" name="password" size="20" type="password" tabindex="3" value=""/>
                    </th>
                </tr>
                <tr>
                    <th class="pad2">
                        <label for="email">{Input_Email}</label>
                    </th>
                    <th class="pad2">
                        <input name="email" id="email" class="pad2 wid" size="20" type="text" tabindex="4" value=""/>
                    </th>
                </tr>
                <tr>
                    <th class="pad2">
                        <label for="galaxy">{Input_Galaxy}</label>
                    </th>
                    <th class="pad2">
                        <input name="galaxy" id="galaxy" class="pad2" style="width: 50px;" type="text" tabindex="5" value="1"/>
                    </th>
                </tr>
                <tr>
                    <th class="pad2">
                        <label for="rules">{Input_Rules}</label> <a class="TextShadow" href="rules.php" target="_blank"><u>Regulamin</u></a>
                    </th>
                    <th class="pad2">
                        <input name="rules" id="rules" type="checkbox" tabindex="6"/>
                    </th>
                </tr>
                <tr>
                    <th class="pad2" colspan="2">
                        <center style="min-height: 110px;">{ReCaptchaCode}</center>
                    </th>
                </tr>
                <tr>
                    <th class="pad3" colspan="2">
                        <input id="submitForm" name="submit" class="pad3 fntBold w90p" type="submit" value="{signup}" tabindex="8"/>
                    </th>
                </tr>
            </tbody>
        </table>
    </form>
</div>
