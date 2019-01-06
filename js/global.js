/* globals PHPVar */

$(document).ready(function () {
    $("#clockDiv").qtip({
        content: PHPVar["ServerTimeTxt"],
        style: {
            classes: "tiptip_content"
        },
        position: {
            my: "top center",
            at: "bottom center",
            adjust: {
                y: 4
            }
        }
    });
    var TopMenuServerClientDifference = (PHPVar["ServerTimestamp"] * 1000) - new Date().getTime();
    setInterval(function () {
        var Now = new Date(new Date().getTime() + TopMenuServerClientDifference);
        var Hour = Now.getHours();
        var Min = Now.getMinutes();
        var Sec = Now.getSeconds();
        var Day = Now.getDate();
        var Month = Now.getMonth() + 1;
        var Year = Now.getFullYear();

        if (Hour < 10) {
            Hour = "0" + Hour;
        }
        if (Min < 10) {
            Min = "0" + Min;
        }
        if (Sec < 10) {
            Sec = "0" + Sec;
        }
        if (Day < 10) {
            Day = "0" + Day;
        }
        if (Month < 10) {
            Month = "0" + Month;
        }

        $("#clock").html(Day + "." + Month + "." + Year + " <b>" + Hour + ":" + Min + ":" + Sec + "</b>");
    }, 250);
});
