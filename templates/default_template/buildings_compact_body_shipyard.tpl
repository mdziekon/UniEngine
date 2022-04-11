<script>
var AllowPrettyInputBox= {P_AllowPrettyInputBox};
var ShowElementOnStartup = '{Create_ShowElementOnStartup}';
var RunQueueHandler = '{Create_RunQueueJSHandler}';
var LastQueueID = {Create_LastJSQueueID};
var Resources = {
    'metal': '{Create_MetalMax}',
    'crystal': '{Create_CrystalMax}',
    'deuterium': '{Create_DeuteriumMax}'
};
var JSLang = {
    'InfoBox_ShowResReq': '{InfoBox_ShowResReq}',
    'InfoBox_ShowTechReq': '{InfoBox_ShowTechReq}',
    'Metal': '{Metal}',
    'Crystal': '{Crystal}',
    'Deuterium': '{Deuterium}',
    'Energy': '{Energy}',
    'DarkEnergy': '{DarkEnergy}'
};
var ShipPrices = {Create_InsertPrices};
var ShipTimes = {Create_InsertTimes};
var QueueArray = {Create_QueueJSArray};

function onQueuesFirstElementFinished (redirectPageType) {
    window.setTimeout(function () {
        document.location.href = "buildings.php?mode=" + redirectPageType;
    }, 1000);
}

</script>
<script src="dist/js/_libCommon.cachebuster-1649555016585.min.js"></script>
<script src="dist/js/structures.cachebuster-1649642056932.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="dist/css/structures.cachebuster-1546565145290.min.css" />
<div style="height: 6px;"></div>
<div class="w870x">
    <div style="float: left; width: 600px;">
        <table class="w100p">
            <tr>
                <th style="height: 180px; padding: 3px;">
                    <table class="w100p h100p" id="nfoElements">
                        <tr id="nfoEl_0">
                            <td class="infoImg MediumImg">
                                <img src="{Insert_SkinPath}planeten/{Insert_PlanetImg}.jpg" class="infoImg MediumImg"/>
                            </td>
                            <td class="infoDes MediumImg">
                                <b id="plInfoName">{Insert_PlanetType} "{Insert_PlanetName}" <a href="galaxy.php">[{Insert_PlanetPos_Galaxy}:{Insert_PlanetPos_System}:{Insert_PlanetPos_Planet}]</a></b><br/><br/>
                                <b id="plInfoDet">
                                    <span class="plInfo_Label">{Overview_ShipyardLevel}:</span> {Insert_Overview_ShipyardLevel}<br/>
                                    <span class="plInfo_Label">{Overview_NanoFactoryLevel}:</span> {Insert_Overview_NanoFactoryLevel}<br/>
                                    <span class="plInfo_Label">{Overview_Temperature}:</span> {Insert_Overview_Temperature}<br/>
                                    <span class="plInfo_Label">{Overview_SolarSateliteEnergy}:</span> <b class="lime">+{Insert_Overview_SolarSateliteEnergy}</b>
                                </b>
                            </td>
                        </tr>
                        {Create_ElementsInfoBoxes}
                    </table>
                </th>
            </tr>
        </table>
        <table class="w100p">
            <tr>
                <td class="c center">{ListBox_ShipsList}</td>
            </tr>
            <tr>
                <th class="pad5">
                    <form action="" method="post" style="margin: 0px;" id="shipyardForm">
                        <input type="hidden" name="cmd" value="exec"/>
                        {Create_StructuresList}
                    </form>
                </th>
            </tr>
        </table>
    </div>
    <div class="fr">
        <table class="w260x mb10">
            <tr>
                <td class="c center" colspan="2">{Cart_Header}</td>
            </tr>
            <tr>
                <th class="pad2 w50x" rowspan="3">{Cart_Cost}</th>
                <th class="pad2">
                    <img src="{SkinPath}images/metall.gif" class="infoRes_metal fl"/>
                    <div class="fr resCounter" id="resC_metal">0</div>
                </th>
            </tr>
            <tr>
                <th class="pad2">
                    <img src="{SkinPath}images/kristall.gif" class="infoRes_crystal fl"/>
                    <div class="fr resCounter" id="resC_crystal">0</div>
                </th>
            </tr>
            <tr>
                <th class="pad2">
                    <img src="{SkinPath}images/deuterium.gif" class="infoRes_deuterium fl"/>
                    <div class="fr resCounter" id="resC_deuterium">0</div>
                </th>
            </tr>
            <tr>
                <th class="pad2 w50x">{Cart_Time}</th>
                <th class="pad2" id="timeC">{Create_PrettyTimeZero}</th>
            </tr>
            <tr>
                <th class="pad2 center" colspan="2">
                    <a href="#" id="buttonC" class="infoButton construct_Gray">
                        <span class="infoButtonText">{Cart_Construct}<br />{Cart_Ships}</span>
                    </a>
                </th>
            </tr>
        </table>
        <table class="w260x">
            <tr>
                <td class="c center" colspan="2">{Queue_Header}</td>
            </tr>
            {Create_Queue}
        </table>
    </div>
</div>
