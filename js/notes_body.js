/* globals JSLang */

$(document).ready(function () {
    var MainForm = $("#thisForm");
    var Checkboxes = $("input[type=\"checkbox\"]:not(#selAll)", MainForm);
    MainForm.submit(function () {
        if ($("select[name=\"action\"]").val() == 2) {
            return confirm(JSLang["JS_Confirm"]);
        }
    });

    $("#selAll").click(function () {
        $("input[type=\"checkbox\"]", MainForm).attr("checked", $(this).is(":checked"));
    });

    Checkboxes.click(function () {
        var AllChecked = true;
        Checkboxes.each(function () {
            if (!$(this).is(":checked")) {
                AllChecked = false;
                return;
            }
        });
        $("#selAll").attr("checked", AllChecked);
    });
});
