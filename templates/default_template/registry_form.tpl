<script>
var JSLang = {Insert_JSLang};
var phpVars = {
    domain: "{phpVars_domain}",
    unidata: {phpVars_unidata}
};
</script>
<script type="text/javascript" src="libs/jquery-cookie/jquery.cookie-1.0.0.1.min.js"></script>
<script src="dist/js/register.cachebuster-1561838018325.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="dist/css/register.cachebuster-1546564327123.min.css" />
<br/>
<div>
    <h2 class="TextShadow">
        <font size="+3">{Title}</font>
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
                        <input class="pad2 wid" id="username" name="username" style="width: 200px;" maxlength="64" type="text" tabindex="2" value=""/>
                    </th>
                </tr>
                <tr>
                    <th class="pad2">
                        <label for="password">{Input_Passoword}</label>
                    </th>
                    <th class="pad2">
                        <input class="pad2 wid" id="password" name="password" style="width: 200px;" type="password" tabindex="3" value=""/>
                    </th>
                </tr>
                <tr>
                    <th class="pad2">
                        <label for="email">{Input_Email}</label>
                    </th>
                    <th class="pad2">
                        <input name="email" id="email" class="pad2 wid" style="width: 200px;" type="text" tabindex="4" value=""/>
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
                        <label for="lang">{Input_Language}</label>
                    </th>
                    <th class="pad2">
                        <select name="lang" id="lang" class="pad2 wid" style="width: 200px;" type="text" tabindex="6">
                            {Insert_PreselectedUniLanguages}
                        </select>
                    </th>
                </tr>
                <tr>
                    <th class="pad2">
                        <label for="rules">{Input_Rules}</label> <a class="TextShadow" href="rules.php" target="_blank"><u>{Input_RulesLinkLabel}</u></a>
                    </th>
                    <th class="pad2">
                        <input name="rules" id="rules" type="checkbox" tabindex="7"/>
                    </th>
                </tr>
                <tr>
                    <th
                        class="pad2"
                        colspan="2"
                    >
                        <div style="width: 100%; text-align: center;">
                            <div
                                class="captcha-container"
                                style="display: inline-block"
                            ></div>
                        </div>
                        <input name="captcha_response" id="captcha_response" type="hidden"/>
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
{PHPInject_RecaptchaJSSetup}
