/* globals JSLang */

var DateTimePickerSettings = {
    showButtonPanel: false,
    beforeShow: function () {
        setTimeout(function () {
            $("#ui-datepicker-div").css({"z-index": 1000});
        }, 1);
    }
};

$(document).ready(function () {
    $("[name=\"last_rules_changes\"]").datetimepicker(DateTimePickerSettings);

    $("input:not([type=\"hidden\"]), textarea", $("#thisForm")).each(function () {
        if ($(this).attr("type") == "checkbox") {
            $(this).data("defVal", $(this).is(":checked"));
        } else {
            $(this).data("defVal", $(this).val());
        }
    });

    $("a.doReset").click(function () {
        var Element = $(this).parents("tr").find("[name]");
        if (Element.attr("type") == "checkbox") {
            Element.attr("checked", Element.data("defVal"));
        } else {
            Element.val(Element.data("defVal"));
        }

        return false;
    });

    $("#ConfigReload").click(function () {
        if (window.location.href.indexOf("?configcachereload=1") === -1) {
            window.location.href += "?configcachereload=1";
        } else {
            window.location.reload();
        }
    });

    $("#thisForm").submit(function () {
        var SomethingChanged = false;
        $(".needConfirm", $(this)).each(function () {
            if ($(this).attr("type") == "checkbox") {
                if ($(this).is(":checked") != $(this).data("defVal")) {
                    SomethingChanged = true;
                    return false;
                }
            } else {
                if ($(this).val() != $(this).data("defVal")) {
                    SomethingChanged = true;
                    return false;
                }
            }
        });

        if (SomethingChanged === true) {
            return confirm(JSLang["JS_ConfirmNeeded"]);
        }
    });
});
