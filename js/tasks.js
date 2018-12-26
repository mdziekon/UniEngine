/* globals OverrideTab, AddLocationMode, SkipConfirmText, SetActiveTask */

$(document).ready(function () {
    var TabsList = $("[id^=\"TaskTab\"]");

    $("[id^=\"ShowTask_\"]").hide(0);

    $("#tab_" + OverrideTab).addClass("tabSelect");
    $(".tab,.tab1")
        .hover(
            function () {
                $(this).addClass("tabHover");
            },
            function () {
                $(this).removeClass("tabHover");
            }
        )
        .click(function () {
            var ThisID = $(this).attr("id");
            if (ThisID !== "tPrev" && ThisID !== "tNext") {
                var ThisTabClass = false;
                if ($(this).hasClass("tab")) {
                    ThisTabClass = "tab";
                } else if ($(this).hasClass("tab1")) {
                    ThisTabClass = "tab1";
                }
                $("." + ThisTabClass).removeClass("tabSelect");
                $(this).removeClass("tabHover").addClass("tabSelect");
            } else {
                var ThisIndex = TabsList.index($("#" + $(".tab1.tabSelect").attr("id")));
                if (ThisID === "tPrev") {
                    if (ThisIndex == 0) {
                        return;
                    }
                    TabsList.eq(ThisIndex - 1).click();
                } else {
                    TabsList.eq(ThisIndex + 1).click();
                }
            }
        });
    $("[id^=\"Link_TaskTab_\"]")
        .hover(
            function () {
                $("#TaskTab_" + $(this).attr("id").replace("Link_TaskTab_", "")).mouseover();
            },
            function () {
                $("#TaskTab_" + $(this).attr("id").replace("Link_TaskTab_", "")).mouseout();
            }
        )
        .click(function () {
            $("#TaskTab_" + $(this).attr("id").replace("Link_TaskTab_", "")).click();
            return false;
        });

    $(".tab").click(function () {
        window.location.href = "?mode=" + $(this).attr("id").replace("tab_", "");
    });
    $("[id^=\"TaskTab_\"]").click(function () {
        $("[id^=\"ShowTask_\"]").hide(0);
        $("#ShowTask_" + $(this).attr("id").replace("TaskTab_", "")).show(0);
    });

    $(".goCat").click(function () {
        window.location.href = "?" + AddLocationMode + "cat=" + $(this).attr("id").replace("cat_", "");
    });
    $("[id^=\"skip_\"]").click(function () {
        if (confirm(SkipConfirmText)) {
            window.location.href = "?skipcat=" + $(this).attr("id").replace("skip_", "");
        }
    });

    $(".help").tipTip({delay: 0, maxWidth: 500, attribute: "title", defaultPosition: "top"});

    $("#TaskTab_" + SetActiveTask).click();
});
