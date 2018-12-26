$(document).ready(function () {
    var Offset = 20;
    if ($("#topMenu").html() === null) {
        Offset = 0;
    }

    if (location.href.indexOf("#rule_") !== -1) {
        var ElementID = location.href.split("#rule_")[1];
        $("html, body").scrollTop($("#rule_" + ElementID).offset().top - Offset);
    }

    $("a[href*=\"#rule_\"]").click(function () {
        var Current = $(window).scrollTop();
        var ElementID = $(this).attr("href").split("#rule_")[1];
        location.hash = "rule_" + ElementID;
        $("html, body").scrollTop(Current);
        $("html, body").animate({"scrollTop": $("#rule_" + ElementID).offset().top - Offset});
        return false;
    });
});
