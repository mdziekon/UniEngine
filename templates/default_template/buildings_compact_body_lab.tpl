<script>
var ShowElementOnStartup = '{Create_ShowElementOnStartup}';
var RunQueueHandler = 'false';
var JSLang = {
    'InfoBox_ShowResReq': '{InfoBox_ShowResReq}',
    'InfoBox_ShowTechReq': '{InfoBox_ShowTechReq}',
    'Metal': '{Metal}',
    'Crystal': '{Crystal}',
    'Deuterium': '{Deuterium}',
    'Energy': '{Energy}',
    'DarkEnergy': '{DarkEnergy}',
    'Queue_CantCancel_Premium': '{Queue_CantCancel_Premium}',
    'Queue_ConfirmCancel': '{Queue_ConfirmCancel}',
    'Queue_Cancel_Go': '{Queue_Cancel_Go}'
};

function onQueuesFirstElementFinished () {
    $("#QueueCancel")
        .html(JSLang['Queue_Cancel_Go'])
        .attr("href", "buildings.php?mode=research")
        .removeClass("cancelQueue")
        .addClass("lime");

    window.setTimeout(function () {
        document.location.href = "buildings.php?mode=research";
    }, 1000);
}
</script>
<script src="dist/js/structures.cachebuster-1580668095015.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="dist/css/structures.cachebuster-1546565145290.min.css" />
<div style="height: 6px;"></div>
<div class="w900x">
    <div style="float: left; width: 600px;">
        <table class="w100p">
            <tr>
                <th style="height: 220px; padding: 3px;">
                    <table class="w100p h100p" id="nfoElements">
                        <tr id="nfoEl_0">
                            <td class="infoImg BigImg">
                                <img src="{Insert_SkinPath}planeten/{Insert_PlanetImg}.jpg" class="infoImg BigImg"/>
                            </td>
                            <td class="infoDes BigImg">
                                <b id="plInfoName">{Insert_PlanetType} "{Insert_PlanetName}" <a href="galaxy.php">[{Insert_PlanetPos_Galaxy}:{Insert_PlanetPos_System}:{Insert_PlanetPos_Planet}]</a></b><br/><br/>
                                <b id="plInfoDet">
                                    <span class="plInfo_Label">{Overview_LabLevel}:</span> {Insert_Overview_LabLevel}<br/>
                                    <span class="plInfo_Label">{Overview_LabsConnected}:</span> {Insert_Overview_LabsConnected}/{Insert_Overview_TotalLabsCount}<br/>
                                    <span class="plInfo_Label">{Overview_LabPower}:</span> {Insert_Overview_LabPower}/{Insert_Overview_LabPowerTotal}
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
                <td class="c center">{ListBox_TechsList}</td>
            </tr>
            <tr>
                <th class="pad1" style="padding-top: 5px;">{Create_StructuresList}</th>
            </tr>
        </table>
    </div>
    <div class="fr">
        <table style="width: 290px;">
            <tr>
                <td class="c center" colspan="2">{Queue_Header}</td>
            </tr>
            {Create_Queue}
        </table>
    </div>
</div>
<div></div>
