/* globals JSLang */

$(document).ready(function () {
    var $SelectAll = $(".SelectAll_Page");
    var $CheckBoxes = $("input[name^=\"msg\"]");
    var ToolTipStyle = {
        style:
        {
            classes: "tiptip_content"
        },
        position:
        {
            my: "top center",
            at: "bottom center"
        }
    };

    // ToolTip Handler
    $SelectAll.qtip($.extend({content: JSLang["JSTip_SelectAll"]}, ToolTipStyle));
    $(".delID").qtip($.extend({content: JSLang["JSTip_DeleteID"]}, ToolTipStyle));

    // Checkbox Handlers
    $SelectAll.click(function () {
        var isChecked = $(this).is(":checked");
        $CheckBoxes.prop("checked", isChecked);
        $SelectAll.prop("checked", isChecked);
    });
    $CheckBoxes.click(function () {
        var isChecked = $(this).is(":checked");
        if (isChecked === true) {
            var AllSelected = true;
            $CheckBoxes.each(function () {
                if ($(this).is(":checked") !== true) {
                    AllSelected = false;
                    return;
                }
            });
            if (AllSelected !== false) {
                $SelectAll.prop("checked", true);
            }
        } else {
            $SelectAll.prop("checked", false);
        }
    });

    // Form Control
    $(".cmd_DelSelected").click(function () {
        var isChecked = false;
        $CheckBoxes.each(function () {
            if ($(this).is(":checked")) {
                isChecked = true;
                return;
            }
        });
        if (isChecked === false) {
            alert(JSLang["JSAlert_NothingSelected"]);
            return false;
        }
        $("input[name=\"cmd\"]").val("DelSelected");
        $("#thisForm").submit();
    });

    $(".delID").click(function () {
        $SelectAll.prop("checked", false);
        $CheckBoxes.prop("checked", false);
        $("input[name=\"msg[" + $(this).attr("data-ID") + "]\"]").prop("checked", true);
        $(".cmd_DelSelected").click();
    });
});
