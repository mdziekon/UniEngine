$(document).ready(function () {
    var Offset = 20;

    if (location.href.indexOf("#el") !== -1) {
        var ElementID = location.href.split("#el")[1];
        $("html, body").scrollTop($("#el" + ElementID).offset().top - Offset);
        $(".selected").removeClass("selected");
        if (ElementID % 100 > 0) {
            $("#el" + ElementID).children().addClass("selected");
        }
    }

    $(".move").click(function () {
        $(this).children()[0].click();
    }).hover(function () {
        $(this).addClass("hover");
    }, function () {
        $(this).removeClass("hover");
    });

    $("a").click(function () {
        if ($(this).attr("href").indexOf("#el") !== -1) {
            var Current = $(window).scrollTop();
            var ElementID = $(this).attr("href").split("#el")[1];
            location.hash = "el" + ElementID;
            $("html, body").scrollTop(Current);
            $("html, body").animate({"scrollTop": $("#el" + ElementID).offset().top - Offset});
            $(".selected").removeClass("selected");
            if (ElementID % 100 > 0) {
                $("#el" + ElementID).children().addClass("selected");
            }
            return false;
        }
    });

    $("[id^=\"el\"]>th").hover(function () {
        $(this).parent().children().addClass("hover");
    }, function () {
        $(this).parent().children().removeClass("hover");
    });
});
