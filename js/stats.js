/* globals JSVars */

$(document).ready(function () {
    $("[class^=\"row_\"]").hover(
        function () {
            $(this).children().addClass("hover");
        },
        function () {
            $(this).children().removeClass("hover");
        });

    $("#prev").click(function () {
        var el = $("#range");
        if (el.children().length > 1) {
            if (!el.children(":first-child").is(":selected")) {
                el.children(":selected").prev().attr("selected", true);
                $("#range").change();
            }
        }
    });
    $("#next").click(function () {
        var el = $("#range");
        if (el.children().length > 1) {
            if (!el.children(":last-child").is(":selected")) {
                el.children(":selected").next().attr("selected", true);
                $("#range").change();
            }
        }
    });
    $("[name=who],[name=type],[name=range]").change(function () {
        if ($(this).attr("name") == "who" && $(this).val() != JSVars["LastWhoVal"]) {
            $("#range").children(":first-child").attr("selected", true);
        }
        $("#statForm").submit();
    });

    var qtipObj = {show: {effect: false}, hide: {effect: false}, style:{classes:"tiptip_content", width: 150}, position:{my:"top center",at:"bottom center", adjust:{y:5}}};
    $(".qChg").qtip($.extend({content: "<div class=\"center\">" + JSVars["QuickChangeInfo"] + "</div>"}, qtipObj));
    $(".dChg").qtip($.extend({content: "<div class=\"center\">" + JSVars["DailyChangeInfo"] + "</div>"}, qtipObj));
});
