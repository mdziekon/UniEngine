/* globals View_Mode */

$(document).ready(function () {
    $("#showView").click(function () {
        $("#viewOff").animate({bottom: "-=30"}, 500, function () {
            $("#viewOn").animate({bottom: "+=30"}, 500);
        });
    });
    $("#hideView").click(function () {
        $("#viewOn").animate({bottom: "-=30"}, 500, function () {
            $("#viewOff").animate({bottom: "+=30"}, 500);
        });
    });

    $(".modeSelector").click(function () {
        if (!$(this).hasClass("selected")) {
            $("#chgViewIn").val($(this).attr("id").replace("viewMode_", "")).parent().submit();
        }
    });

    $("#viewOff").animate({bottom: "+=30"}, 0);
    $("#viewMode_" + View_Mode).addClass("selected");
});
