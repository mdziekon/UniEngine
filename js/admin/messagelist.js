/* globals JS_Lang */

$(document).ready(function () {
    var MsgRows = $(".msgrow");
    var AllSelects = $("[name^=\"sele[\"]");
    var AllCheckboxes = $("[name^=\"sele[\"], .selAll");

    $("a", MsgRows).each(function () {
        if (typeof $(this).attr("href") === "undefined" || $(this).attr("href") == "#") {
            return;
        }
        $(this).attr("href", "../" + $(this).attr("href")).attr("target", "_blank");
    });

    $("a[onclick^=\"f(\"]", MsgRows).each(function () {
        $(this).attr("onclick", $(this).attr("onclick").replace("f('", "f('../"));
    });

    $("[name=\"page_select\"]").change(function () {
        $("[name=\"page_select\"]").val($(this).val());
        $("#formID").submit();
    });
    $("[name^=setsel],[name^=delsel]").click(function () {
        $("[name=\"stay\"]").val("true");
    });

    $(".selAll").click(function () {
        AllCheckboxes.attr("checked", $(this).is(":checked"));
    });
    AllSelects.click(function () {
        var OneUnselected = false;
        AllSelects.each(function () {
            if (!$(this).is(":checked")) {
                OneUnselected = true;
                return;
            }
        });
        if (OneUnselected) {
            $(".selAll").attr("checked", false);
        } else {
            $(".selAll").attr("checked", true);
        }
    });

    $("#msgRows > tr")
        .hover(function () {
            $(this).children().addClass("hover");
        }, function () {
            $(this).children().removeClass("hover");
        });

    $(".tipBin").tipTip({delay: 300, content: JS_Lang["tip_Deleted"]});
    $(".tipEye").tipTip({delay: 300, content: JS_Lang["tip_Read"]});
    $(".tipCopy").tipTip({delay: 300, content: JS_Lang["tip_Copy"]});
});
