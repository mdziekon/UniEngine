/* exported ReplaceArr, Update, ArrayReplace */

var MToolTip = "";
var CToolTip = "";
var DToolTip = "";
var EToolTip = "";
var Metal = false;
var Crystal = false;
var Deuterium = false;
var UpdaterInit = false;
var Metal_Rest = 0;
var Crystal_Rest = 0;
var Deuterium_Rest = 0;
var red = "red";
var lime = "lime";
var UpdaterObjects = {};

var ReplaceArr = new Array("_ResName_", "_ResIncome_", "_ResFullTime_", "_ResStoreStatus_");
var ResTipStyle = {classes: "tiptip_content ui-tooltip-tipsy ui-tooltip-shadow"};
var qTipSettings = {show: {delay: 0, effect: false}, hide: {delay: 0, effect: false}, style: ResTipStyle};

function intval (mixed_var, base) {
    var tmp;
    var type = typeof (mixed_var);
    if (type === "boolean") {
        return (mixed_var) ? 1 : 0;
    } else if (type === "string") {
        tmp = parseInt(mixed_var, base || 10);
        return (isNaN(tmp) || !isFinite(tmp)) ? 0 : tmp;
    } else if (type === "number" && isFinite(mixed_var)) {
        return Math.floor(mixed_var);
    } else {
        return 0;
    }
}

function number_format (value) {
    value += "";
    var rgx = /(\d+)(\d\d\d)/;
    while (rgx.test(value)) {
        value = value.replace(rgx, "$1" + "." + "$2");
    }
    return value;
}

function Update (
    Met_PerHour,
    Cry_PerHour,
    Deu_PerHour,
    Met_Max,
    Cry_Max,
    Deu_Max,
    Met_MaxOver,
    Cry_MaxOver,
    Deu_MaxOver,
    Met_Cur,
    Cry_Cur,
    Deu_Cur
) {
    var setMetalColor;
    var setCrystalColor;
    var setDeuteriumColor;

    if (UpdaterInit === false) {
        UpdaterObjects.Metal = $("#metal");
        UpdaterObjects.Crystal = $("#crystal");
        UpdaterObjects.Deuterium = $("#deut");
        UpdaterObjects.MetalMax = $("#metalmax");
        UpdaterObjects.CrystalMax = $("#crystalmax");
        UpdaterObjects.DeuteriumMax = $("#deuteriummax");
        UpdaterInit = true;
    }

    if (Metal === false) {
        Metal = intval(Met_Cur);
    }
    if (Crystal === false) {
        Crystal = intval(Cry_Cur);
    }
    if (Deuterium === false) {
        Deuterium = intval(Deu_Cur);
    }
    if (Metal < Met_MaxOver) {
        var Met_PerSec = Met_PerHour / 3600;
        var IntMet_PerSec = intval(Met_PerSec);
        var Met_Rest = Met_PerSec - IntMet_PerSec;
        Metal += IntMet_PerSec;
        Metal_Rest += Met_Rest;
        if (Metal_Rest > 1) {
            Metal += 1;
            Metal_Rest -= 1;
        }
        if (Metal > Met_Max) {
            setMetalColor = red;
        } else {
            setMetalColor = lime;
        }
        if (Metal > Met_MaxOver) {
            Metal = intval(Met_MaxOver);
        }
    } else {
        setMetalColor = red;
    }
    if (Crystal < Cry_MaxOver) {
        var Cry_PerSec = Cry_PerHour / 3600;
        var IntCry_PerSec = intval(Cry_PerSec);
        var Cry_Rest = Cry_PerSec - IntCry_PerSec;
        Crystal += IntCry_PerSec;
        Crystal_Rest += Cry_Rest;
        if (Crystal_Rest > 1) {
            Crystal += 1;
            Crystal_Rest -= 1;
        }
        if (Crystal > Cry_Max) {
            setCrystalColor = red;
        } else {
            setCrystalColor = lime;
        }
        if (Crystal > Cry_MaxOver) {
            Crystal = intval(Cry_MaxOver);
        }
    } else {
        setCrystalColor = red;
    }
    if (Deuterium < Deu_MaxOver || Deu_PerHour < 0) {
        var Deu_PerSec = Deu_PerHour / 3600;
        var IntDeu_PerSec = intval(Deu_PerSec);
        var Deu_Rest = Deu_PerSec - IntDeu_PerSec;
        Deuterium += IntDeu_PerSec;
        Deuterium_Rest += Deu_Rest;
        if (Deuterium_Rest > 1) {
            Deuterium += 1;
            Deuterium_Rest -= 1;
        }
        if (Deuterium > Deu_Max) {
            setDeuteriumColor = red;
        } else {
            setDeuteriumColor = lime;
        }
        if (Deuterium > Deu_MaxOver) {
            if (Deu_PerHour > 0) {
                Deuterium = intval(Deu_MaxOver);
            }
        }
    } else {
        setDeuteriumColor = red;
    }

    UpdaterObjects.Metal.html(number_format(Metal)).css("color", setMetalColor);
    UpdaterObjects.Crystal.html(number_format(Crystal)).css("color", setCrystalColor);
    UpdaterObjects.Deuterium.html(number_format(Deuterium)).css("color", setDeuteriumColor);
    UpdaterObjects.MetalMax.css("color", setMetalColor);
    UpdaterObjects.CrystalMax.css("color", setCrystalColor);
    UpdaterObjects.DeuteriumMax.css("color", setDeuteriumColor);
}

function ArrayReplace (Org, Search, Replace) {
    var SearchLen = Search.length;
    for (var i = 0; i < SearchLen; i += 1) {
        Org = Org.replace(Search[i], Replace[i]);
    }
    return Org;
}

$(document).ready(function () {
    var ResElements = {
        "met": $("#resMet"),
        "cry": $("#resCry"),
        "deu": $("#resDeu"),
        "eng": $("#resEng")
    };
    var PlanetList = $("#planet");

    if ($("#plType").is(":visible")) {
        $(".plBut").width((PlanetList.width() / 2) - $("#plType").width() + 2);
    } else {
        $(".plBut").width((PlanetList.width() / 2) + 1);
    }

    // PlanetList Handler
    $("#prevPl").click(function () {
        if (PlanetList.children().length > 1) {
            if (PlanetList.children(":first-child").is(":selected")) {
                PlanetList.children(":last-child").attr("selected", true);
            } else {
                PlanetList.children(":selected").prev().attr("selected", true);
            }
            PlanetList.change();
        }
    });
    $("#nextPl").click(function () {
        if (PlanetList.children().length > 1) {
            if (PlanetList.children(":last-child").is(":selected")) {
                PlanetList.children(":first-child").attr("selected", true);
            } else {
                PlanetList.children(":selected").next().attr("selected", true);
            }
            PlanetList.change();
        }
    });
    PlanetList.change(function () {
        window.location = String($(this).val());
    });
    $("#plType").click(function () {
        PlanetList.val($("option[value*=\"cp=" + $(this).attr("data-id") + "\"]", PlanetList).val()).change();
    });

    // qTips
    ResElements["met"].qtip(
        $.extend(
            {
                content: MToolTip,
                position: {
                    target: $("#metalmax"),
                    my: "top center",
                    at: "bottom center",
                    adjust: { y: 10 }
                }
            },
            qTipSettings
        )
    );
    $(".resMet:not(#resMet)").hover(function () {
        ResElements["met"].mouseover();
    }, function () {
        ResElements["met"].mouseout();
    });

    ResElements["cry"].qtip(
        $.extend(
            {
                content: CToolTip,
                position: {
                    target: $("#crystalmax"),
                    my: "top center",
                    at: "bottom center",
                    adjust: { y: 10 }
                }
            },
            qTipSettings
        )
    );
    $(".resCry:not(#resCry)").hover(function () {
        ResElements["cry"].mouseover();
    }, function () {
        ResElements["cry"].mouseout();
    });

    ResElements["deu"].qtip(
        $.extend(
            {
                content: DToolTip,
                position: {
                    target: $("#deuteriummax"),
                    my: "top center",
                    at: "bottom center",
                    adjust: { y: 10 }
                }
            },
            qTipSettings
        )
    );
    $(".resDeu:not(#resDeu)").hover(function () {
        ResElements["deu"].mouseover();
    }, function () {
        ResElements["deu"].mouseout();
    });

    ResElements["eng"].qtip(
        $.extend(
            {
                content: EToolTip,
                position: {
                    target: $("#showET"),
                    my: "top center",
                    at: "bottom center",
                    adjust: { y: 10 }
                }
            },
            qTipSettings
        )
    );
    $(".resEnr:not(#resEng)").hover(function () {
        ResElements["eng"].mouseover();
    }, function () {
        ResElements["eng"].mouseout();
    });
});
