$(document).ready(function () {
    function checkAllStates () {
        var UncheckedCount = 0;
        var UncheckedCivil = 0;
        var UncheckedMilitary = 0;

        MissionSelectors.each(function () {
            if ($(this).is(":checked")) {
                return;
            }

            UncheckedCount += 1;
            if ($(this).hasClass("civil")) {
                UncheckedCivil += 1;
            } else {
                UncheckedMilitary += 1;
            }
        });

        if (UncheckedCount == 0) {
            MissionAll.attr("checked", true);
        } else {
            MissionAll.attr("checked", false);
        }
        if (UncheckedCivil == 0) {
            MissionCivil.attr("checked", true);
        } else {
            MissionCivil.attr("checked", false);
        }
        if (UncheckedMilitary == 0) {
            MissionMilitary.attr("checked", true);
        } else {
            MissionMilitary.attr("checked", false);
        }
    }

    var DateTimePickerSettings = {
        showButtonPanel: false,
        beforeShow: function () {
            setTimeout(function () {
                $("#ui-datepicker-div").css({"z-index": 1000});
            },1);
        }
    };

    $("[name=\"endTime_date\"]").datetimepicker(DateTimePickerSettings);
    $("[name=\"postEndTime_date\"]").datetimepicker(DateTimePickerSettings);
    $("[name=\"startTime_date\"]").datetimepicker(DateTimePickerSettings);

    $("#postEndTime_Zero").click(function () {
        $("[name=\"postEndTime_date\"]").val("");
    });
    $("#postEndTime_1Day").click(function () {
        var NewDate = new Date($("[name=\"endTime_date\"]").datetimepicker("getDate"));
        $("[name=\"postEndTime_date\"]").datetimepicker("setDate", new Date(NewDate.getTime() + (24 * 60 * 60 * 1000)));
    });
    $("#goBack").click(function () {
        window.location.href = "?";
    });

    var MissionSelectors = $("[name^=\"mission\"]");
    var MissionSelectorsCivil = $(".civil");
    var MissionSelectorsMilitary = $(".military");
    var MissionAll = $("#missionAll");
    var MissionCivil = $("#missionCivil");
    var MissionMilitary = $("#missionMilitary");

    MissionSelectors.click(function () {
        checkAllStates();
    });

    MissionAll.click(function () {
        var ThisState = $(this).is(":checked");
        MissionSelectors.attr("checked", ThisState);
        MissionCivil.attr("checked", ThisState);
        MissionMilitary.attr("checked", ThisState);
    });
    MissionCivil.click(function () {
        var ThisState = $(this).is(":checked");
        MissionSelectorsCivil.each(function () {
            $(this).attr("checked", ThisState);
        });
        checkAllStates();
    });
    MissionMilitary.click(function () {
        var ThisState = $(this).is(":checked");
        MissionSelectorsMilitary.each(function () {
            $(this).attr("checked", ThisState);
        });
        checkAllStates();
    });

    checkAllStates();
});
