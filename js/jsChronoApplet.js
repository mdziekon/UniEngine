/* globals ServerClientDifference, JSChronoAppletLang */
/* exported CreateChronoApplet */

function CreateChronoApplet (Type, Ref, EndTime, Reverse, EndCallback) {
    if (Reverse === undefined || Reverse === null || Reverse === false) {
        Reverse = false;
    } else {
        Reverse = true;
    }
    var CurrentTime = new Date().getTime() + ServerClientDifference;
    var Difference  = Math.floor(((EndTime * 1000) - CurrentTime) / 1000);
    if (Reverse === true) {
        Difference = (-1) * Difference;
    }

    var SetTimer;

    if (Difference <= 0) {
        SetTimer = "-";
        clearInterval(eval("ChronoInterval" + Type + "" + Ref));
        if (EndCallback !== undefined && EndCallback !== null) {
            EndCallback();
        }
    } else {
        var Days;

        if (Difference >= 86400) {
            Days = Math.floor(Difference / 86400);
            Difference -= Days * 86400;
        } else {
            Days = 0;
        }

        var Hours       = Math.floor(Difference / 3600);
        Difference     -= Hours * 3600;
        var Minutes     = Math.floor(Difference / 60);
        Difference     -= Minutes * 60;
        var Seconds     = Difference;

        if (Hours < 10) {
            Hours   = "0" + Hours;
        }
        if (Minutes < 10) {
            Minutes = "0" + Minutes;
        }
        if (Seconds < 10) {
            Seconds = "0" + Seconds;
        }
        SetTimer = Hours + ":" + Minutes + ":" + Seconds;
        if (Days > 0) {
            if (Days == 1) {
                SetTimer = "1 " + JSChronoAppletLang.Lang_day1 + " " + SetTimer;
            } else {
                SetTimer = Days + " " + JSChronoAppletLang.Lang_dayM + " " + SetTimer;
            }
        }
    }

    var BoxName = "bxx" + Type + "" + Ref;
    if (document.getElementById(BoxName) != null) {
        document.getElementById(BoxName).innerHTML = SetTimer;
    }
}
