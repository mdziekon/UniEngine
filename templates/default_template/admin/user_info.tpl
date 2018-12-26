<style>
.pad {
    padding: 5px;
}
.mark {
    width: 100px;
}
.fArr, .fCar, .mark {
    cursor: pointer;
}
.fID {
    font-size: 50%;
}
.markHover {
    background-color: #455B87;
    border-color: #526EA3;
}
.markSelect {
    background-color: #4E6797;
    border-color: #607BAF;
}
.infoTHN {
    width: 20%;
}
.infoTHD {
    width: 30%;
}
.infoTHR {
    border-right-color: #607BAF;
}
.infoTHL {
    border-left-color: #607BAF;
}
.red {
    color: red;
}
.orange {
    color: orange;
}
.lime {
    color: lime;
}
.blue {
    color: #87CEEB;
}
.cent {
    text-align: center;
}
.help_th {
    background: none;
    border: none;
}
.cargo_res {
    min-width: 70px;
}
.aLog {
    padding-left: 18px;
    background: url('../images/log.png') no-repeat;
}
</style>
<script>
    $(document).ready(
        function()
        {
            $("[id^=Cont]").hide(0);
            $("#Mark{MarkSelect}").addClass('markSelect');
            $("#Cont{MarkSelect}").show(0);
            $(".mark").hover(
            function()
            {
                $(this).addClass('markHover');
            },
            function()
            {
                $(this).removeClass('markHover');
            }).click(
            function()
            {
                    $(".mark").each(function(){ $(this).removeClass('markSelect') });
                    $("[id^=Cont]").hide(0);
                    $("#"+$(this).attr('id').replace('Mark', 'Cont')).show(0);
                    $(this).removeClass('markHover').addClass('markSelect');

            });
            $(".help").tipTip({delay: 0, maxWidth: "200px", attribute: 'title'});
            $(".fArr").tipTip({delay: 0, maxWidth: "300px", attribute: 'title'});
            $(".fCar").tipTip({delay: 0, maxWidth: "400px", attribute: 'title'});
        });
</script>
<br />
<table width="900">
    <tbody>
        <tr>
            <th class="pad mark" id="Mark01">{GeneralOverview}</th>
            <th class="pad mark" id="Mark02">{Statistics}</th>
            <th class="pad mark" id="Mark03">{PlanetsInfo}</th>
            <th class="pad mark" id="Mark04">{FleetControl}</th>
        </tr>

        <tr id="Cont01">
            <th colspan="4">
                <table width="100%">
                    <tbody>
                        <tr>
                            <th class="pad infoTHN">{Nickname}</th>
                            <th class="pad infoTHD infoTHR">{PlayerName}</th>
                            <th class="pad infoTHN infoTHL">{DarkEnergy}</th>
                            <th class="pad infoTHD">{PlayerDarkEnergy}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{EMail}</th>
                            <th class="pad infoTHD infoTHR">{PlayerEmail}</th>
                            <th class="pad infoTHN infoTHL">{ProTime}</th>
                            <th class="pad infoTHD">{PlayerProTime}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN"><b class="help" title="{InActivationProc}">{EMail2}</b></th>
                            <th class="pad infoTHD infoTHR">{PlayerEmail2}</th>
                            <th class="pad infoTHN infoTHL">{GeoTime}</th>
                            <th class="pad infoTHD">{PlayerGeoTime}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{LastIP}</th>
                            <th class="pad infoTHD infoTHR">{PlayerLastIP}</th>
                            <th class="pad infoTHN infoTHL">{EngTime}</th>
                            <th class="pad infoTHD">{PlayerEngTime}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{RegisterIP}</th>
                            <th class="pad infoTHD infoTHR">{PlayerRegIP}</th>
                            <th class="pad infoTHN infoTHL">{AdmTime}</th>
                            <th class="pad infoTHD">{PlayerAdmTime}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{LastActivity}</th>
                            <th class="pad infoTHD infoTHR">{PlayerLastActivity}</th>
                            <th class="pad infoTHN infoTHL">{TecTime}</th>
                            <th class="pad infoTHD">{PlayerTecTime}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{Browser}</th>
                            <th class="pad infoTHD infoTHR">{PlayerBrowser}</th>
                            <th class="pad infoTHN infoTHL">{JamTime}</th>
                            <th class="pad infoTHD">{PlayerJamTime}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{Screen}</th>
                            <th class="pad infoTHD infoTHR">{PlayerScreen}</th>
                            <th class="pad infoTHN infoTHL">{AdditionalPlanets}</th>
                            <th class="pad infoTHD">{PlayerAdditionalPlanets}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{RegisterInfo}</th>
                            <th class="pad infoTHD infoTHR">{PlayerRegisterInfo}</th>
                            <th class="pad infoTHN infoTHL">{MotherPlanet}</th>
                            <th class="pad infoTHD">{PlayerMotherPlanet}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{Vacations}</th>
                            <th class="pad infoTHD infoTHR">{PlayerVacations}</th>
                            <th class="pad infoTHN infoTHL">{Ally}</th>
                            <th class="pad infoTHD">{PlayerAlly}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{Ban}</th>
                            <th class="pad infoTHD infoTHR">{PlayerBan}</th>
                            <th class="pad infoTHN infoTHL">{AccountActive}</th>
                            <th class="pad infoTHD">{PlayerAccountActive}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{Delete}</th>
                            <th class="pad infoTHD infoTHR">{PlayerDelete}</th>
                            <th class="pad infoTHN infoTHL">{DisableIPCheck}</th>
                            <th class="pad infoTHD">{PlayerDisableIPCheck}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{OldNick}</th>
                            <th class="pad infoTHD infoTHR">{PlayerOldNick}</th>
                            <th class="pad infoTHN infoTHL">{InvitedBy}</th>
                            <th class="pad infoTHD">{PlayerInvitedBy}</th>
                        </tr>
                    </tbody>
                </table>
            </th>
        </tr>

        <tr id="Cont02">
            <th colspan="4" class="pad">
                {ShowInfoIfStatsUnavailable}
                <table width="100%" {HideStatsIfUnavailable}>
                    <tbody>
                        <tr>
                            <td class="pad c cent" colspan="4">{General}</td>
                        </tr>
                        <tr>
                            <th class="pad" colspan="2">{Pos}</th>
                            <th class="pad" colspan="2">{PlayerGeneralPos}</th>
                        </tr>
                        <tr>
                            <th class="pad" colspan="2">{Change}</th>
                            <th class="pad" colspan="2">{PlayerGeneralChange1}</th>
                        </tr>
                        <tr>
                            <th class="pad" colspan="2">{DayChange}</th>
                            <th class="pad" colspan="2">{PlayerGeneralChange2}</th>
                        </tr>
                        <tr>
                            <th class="pad" colspan="2">{Points}</th>
                            <th class="pad" colspan="2">{PlayerGeneralPoints}</th>
                        </tr>

                        <tr style="visibility: hidden;">
                            <th></th>
                        </tr>

                        <tr>
                            <td class="pad c cent" colspan="2">{Buildings}</td>
                            <td class="pad c cent" colspan="2">{Fleet}</td>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{Pos}</th>
                            <th class="pad infoTHD infoTHR">{PlayerBuildingsPos}</th>
                            <th class="pad infoTHN infoTHL">{Pos}</th>
                            <th class="pad infoTHD">{PlayerFleetPos}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{Change}</th>
                            <th class="pad infoTHD infoTHR">{PlayerBuildingsChange1}</th>
                            <th class="pad infoTHN infoTHL">{Change}</th>
                            <th class="pad infoTHD">{PlayerFleetChange1}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{DayChange}</th>
                            <th class="pad infoTHD infoTHR">{PlayerBuildingsChange2}</th>
                            <th class="pad infoTHN infoTHL">{DayChange}</th>
                            <th class="pad infoTHD">{PlayerFleetChange2}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{Points}</th>
                            <th class="pad infoTHD infoTHR">{PlayerBuildingsPoints}</th>
                            <th class="pad infoTHN infoTHL">{Points}</th>
                            <th class="pad infoTHD">{PlayerFleetPoints}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{GeneralPointsPercent}</th>
                            <th class="pad infoTHD infoTHR">{PlayerBuildingsPercent}</th>
                            <th class="pad infoTHN infoTHL">{GeneralPointsPercent}</th>
                            <th class="pad infoTHD">{PlayerFleetPercent}</th>
                        </tr>

                        <tr style="visibility: hidden;">
                            <th></th>
                        </tr>

                        <tr>
                            <td class="pad c cent" colspan="2">{Research}</td>
                            <td class="pad c cent" colspan="2">{Defence}</td>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{Pos}</th>
                            <th class="pad infoTHD infoTHR">{PlayerResearchPos}</th>
                            <th class="pad infoTHN infoTHL">{Pos}</th>
                            <th class="pad infoTHD">{PlayerDefencePos}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{Change}</th>
                            <th class="pad infoTHD infoTHR">{PlayerResearchChange1}</th>
                            <th class="pad infoTHN infoTHL">{Change}</th>
                            <th class="pad infoTHD">{PlayerDefenceChange1}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{DayChange}</th>
                            <th class="pad infoTHD infoTHR">{PlayerResearchChange2}</th>
                            <th class="pad infoTHN infoTHL">{DayChange}</th>
                            <th class="pad infoTHD">{PlayerDefenceChange2}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{Points}</th>
                            <th class="pad infoTHD infoTHR">{PlayerResearchPoints}</th>
                            <th class="pad infoTHN infoTHL">{Points}</th>
                            <th class="pad infoTHD">{PlayerDefencePoints}</th>
                        </tr>
                        <tr>
                            <th class="pad infoTHN">{GeneralPointsPercent}</th>
                            <th class="pad infoTHD infoTHR">{PlayerResearchPercent}</th>
                            <th class="pad infoTHN infoTHL">{GeneralPointsPercent}</th>
                            <th class="pad infoTHD">{PlayerDefencePercent}</th>
                        </tr>
                    </tbody>
                </table>
            </th>
        </tr>

        <tr id="Cont03">
            <th colspan="4" class="pad">
                Planets Info
            </th>
        </tr>

        <tr id="Cont04">
            <th colspan="4" class="pad">
                {FleetControlContent}
            </th>
        </tr>

    </tbody>
</table>
