$(document).ready(function () {
    $(".pagin").click(function () {
        $("input[name=\"page\"]").val($(this).attr("id").replace("page_", ""));
        $("#thisForm").submit();
    });
    $("#reset").click(function () {
        $("input[name=\"send\"]").val("");
        $("#thisForm").submit();
    });

    var DateTimePickerSettings = {
        showButtonPanel: false,
        beforeShow: function () {
            setTimeout(function () {
                $("#ui-datepicker-div").css({"z-index": 1000});
            },1);
        }
    };

    $("[name=\"date_from\"]").datetimepicker(DateTimePickerSettings);
    $("[name=\"date_to\"]").datetimepicker(DateTimePickerSettings);
});
