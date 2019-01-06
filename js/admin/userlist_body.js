/* globals JSLang, DefaultPerPage */

var MassActionsEl = false;

function ShowMassActions () {
    if (MassActionsEl.data("visible") !== true) {
        MassActionsEl.animate({bottom: "+=40"}, 500);
        MassActionsEl.data("visible", true);
    }
}
function HideMassActions () {
    if (MassActionsEl.data("visible") !== false) {
        MassActionsEl.animate({bottom: "-=40"}, 500);
        MassActionsEl.data("visible", false);
    }
}

$(document).ready(function () {
    var FromPagination = false;
    var FromMassAction = false;
    var ToggleFilterOptImg = $("#toggleFilterOptImg");
    var UsrBoxes = $(".usrBox");
    var UsrBoxSelect_Visible = $("#usrBoxSelect_AllVisible");
    var UsrBoxSelect_Filtered = $("#usrBoxSelect_AllFiltered");
    var TipStyle1 = {maxWidth: "auto", defaultPosition: "top"};
    MassActionsEl = $("#viewOn");

    $("input[name]:not(.dontSaveOld)", $(".negmarg")).each(function () {
        $(this).data("oldVar", $(this).val());
    });

    if (document.cookie.indexOf("ACP_UserList_FilterOpt_Visible") === -1 || document.cookie.indexOf("ACP_UserList_FilterOpt_Visible=false") !== -1) {
        $("#FiltersOpt").hide(0);
    } else {
        $("#FiltersOpt").show(0);
        ToggleFilterOptImg.attr("src", ToggleFilterOptImg.attr("src").replace("expand", "collapse"));
    }

    $("[id^='usrMore']").hide();
    $("[id^='usrActions']").hide();
    $("[id^='usrNo']").click(function () {
        $("#usrMore" + $(this).attr("id").substr(5)).toggle();
        $("#usrActions" + $(this).attr("id").substr(5)).toggle();
    });
    $("tr.usr").children(":not(.cBox)").addClass("pad");
    $("tr.usr:even th").addClass("even");
    $("tr.usr").hover(function () {
        $(this).children().addClass("hover");
    }, function () {
        $(this).children().removeClass("hover");
    });

    $(".usrReg").tipTip({delay: 0, maxWidth: "auto", attribute: "title"});
    $(".usrOL").tipTip({delay: 0, maxWidth: "auto", attribute: "title", minWidth: "200px"});
    $("#SSInfo").tipTip({delay: 0, maxWidth: "200px", attribute: "title", minWidth: "200px"});
    $("#anyip_span").tipTip({delay: 0, maxWidth: "200px", attribute: "title", minWidth: "200px"});

    $("[name='search_user']").tipTip({delay: 0, maxWidth: "200px", minWidth: "200px", content: JSLang["SearchUserTip"]});
    $(".mEmail").tipTip($.extend(TipStyle1, {content: JSLang["mEmail"]}));
    $(".xEmail").tipTip($.extend(TipStyle1, {content: JSLang["xEmail"]}));
    $(".lastIP").tipTip($.extend(TipStyle1, {content: JSLang["lastIP"]}));
    $(".regIP").tipTip($.extend(TipStyle1, {content: JSLang["regIP"]}));
    $(".lkupinfo").tipTip($.extend(TipStyle1, {content: JSLang["LoopupInfo"]}));
    $(".allyOwn").tipTip($.extend(TipStyle1, {content: JSLang["ItsAllyOwner"]}));
    $(".allyReq").tipTip($.extend(TipStyle1, {content: JSLang["AllyRequested"]}));
    $(".banned").tipTip($.extend(TipStyle1, {content: JSLang["PlayerIsBanned"]}));
    $(".vacations").tipTip($.extend(TipStyle1, {content: JSLang["PlayerIsOnVacations"]}));
    $(".button").tipTip({maxWidth: "auto", content: JSLang["ToggleFilters"], defaultPosition: "bottom"});

    UsrBoxSelect_Visible.tipTip({edgeOffset: 8, delay: 200, maxWidth: 400, content: JSLang["SelectAllVisible"], defaultPosition: "top"});
    UsrBoxSelect_Filtered.tipTip({edgeOffset: 8, delay: 200, maxWidth: 400, content: JSLang["SelectAllFiltered"], defaultPosition: "top"});

    $(".usrName").click(function () {
        $("#tiptip_holder").fadeOut(500);
        return false;
    });

    $("#SBy").change(function () {
        $("#anyip_span, .allysearch").hide();

        var ThisSelected = $("#SBy").val();
        if (ThisSelected == "ip") {
            $("#anyip_span").show();
        } else if (ThisSelected == "ally") {
            $(".allysearch").show();
        } else if (ThisSelected == "aid") {
            $(".allyOnRequest").show();
        }
    }).keyup(function () {
        $(this).change();
    });
    $("#PPList").change(function () {
        var SelectedVal = $("#PPList option:selected")[0].value;
        if (SelectedVal != "-") {
            $("#pp").val(SelectedVal);
            $("form")[0].submit();
        }
    });
    $("#reset").click(function () {
        window.location = "userlist.php";
    });
    $("#searchForm").submit(function () {
        if (FromMassAction || FromPagination) {
            $("input[name]:not(.dontSaveOld)", $(".negmarg")).each(function () {
                $(this).val($(this).data("oldVar"));
            });
        } else {
            if ($("#pp").val() == DefaultPerPage) {
                $("#pp").remove();
            } else if ($("#pp").val() == "") {
                var SelectedVal = $("#PPList option:selected")[0].value;
                if (SelectedVal != "-" && SelectedVal != DefaultPerPage) {
                    $("#pp").val(SelectedVal);
                } else {
                    $("#pp").remove();
                }
            }
            $("input[name=\"preserve\"]").val("");
        }
    });

    $(".checkBox").click(function () {
        if ($(this).is(":checked")) {
            $("[class=\"" + $(this).attr("class") + "\"]").attr("checked", false);
            $(this).attr("checked", true);
        }
    });
    $(".checkBoxOne").click(function () {
        if (!$(this).is(":checked")) {
            var ThisElement = $(this);
            $("[class=\"" + $(this).attr("class") + "\"]").each(function () {
                if ($(this)[0] === ThisElement[0]) {
                    return;
                } else {
                    $(this).attr("checked", true);
                    return;
                }
            });
        }
    });

    $(".pagin").click(function () {
        FromPagination = true;
        $("input[name=\"page\"]").val($(this).attr("id").replace("page_", ""));
        $("#searchForm").submit();
    });

    $(".aDel").click(function () {
        var Return = confirm(JSLang["Userlist_ConfirmDelete"]);
        if (Return === true) {
            $("input[name=\"deleteID\"]").val($(this).attr("id").replace("delID_", ""));
            $("#searchForm").submit();
        }
    });

    $(".sortLink").click(function (event) {
        event.preventDefault();
        var RegExp = /^\?cmd=sort&type=(.*?)&mode=(.*?)$/gi;
        var ThisData = RegExp.exec($(this).attr("href"));
        $("input[name=\"type\"]").val(ThisData[1]);
        $("input[name=\"mode\"]").val(ThisData[2]);

        $("#searchForm").submit();
    });

    $("#toggleFilterOpt").click(function () {
        $("#FiltersOpt").toggle(0);
        if ($("#FiltersOpt").is(":visible")) {
            document.cookie = "ACP_UserList_FilterOpt_Visible=true";
            ToggleFilterOptImg.attr("src", ToggleFilterOptImg.attr("src").replace("expand", "collapse"));
        } else {
            document.cookie = "ACP_UserList_FilterOpt_Visible=false";
            ToggleFilterOptImg.attr("src", ToggleFilterOptImg.attr("src").replace("collapse", "expand"));
        }
    });

    UsrBoxSelect_Visible.click(function () {
        if (!$(this).is(":checked")) {
            UsrBoxSelect_Filtered.prop("checked", false);
            HideMassActions();
        } else {
            ShowMassActions();
        }
        UsrBoxes.prop("checked", $(this).is(":checked"));
    });
    UsrBoxSelect_Filtered.click(function () {
        if ($(this).is(":checked")) {
            UsrBoxes.prop("checked", true);
            UsrBoxSelect_Visible.prop("checked", true);
            $("[name=\"useAllFiltered\"]").val("1");
            ShowMassActions();
        } else {
            $("[name=\"useAllFiltered\"]").val("");
        }
    });

    UsrBoxes.click(function () {
        if ($(this).is(":checked")) {
            var AllChecked = true;
            UsrBoxes.each(function () {
                if (!$(this).is(":checked")) {
                    AllChecked = false;
                    return;
                }
            });
            if (AllChecked === true) {
                UsrBoxSelect_Visible.prop("checked", true);
            } else {
                UsrBoxSelect_Visible.prop("checked", false);
            }
            ShowMassActions();
        } else {
            UsrBoxSelect_Visible.prop("checked", false);
            UsrBoxSelect_Filtered.prop("checked", false);
            var SomethingChecked = false;
            UsrBoxes.each(function () {
                if ($(this).is(":checked")) {
                    SomethingChecked = true;
                    return;
                }
            });
            if (SomethingChecked === true) {
                ShowMassActions();
            } else {
                HideMassActions();
            }
        }
    });

    $(".massAction").click(function () {
        $(this).children()[0].click();
    });

    $("#massBan, #massUnban").click(function () {
        if ($(this).attr("id") == "massBan") {
            $("[name=\"massAction\"]").val("ban");
        } else if ($(this).attr("id") == "massUnban") {
            $("[name=\"massAction\"]").val("unban");
        }
        if ($("[name=\"useAllFiltered\"]").val() != "1") {
            var CombineIDs = [];
            UsrBoxes.each(function () {
                if ($(this).is(":checked")) {
                    CombineIDs.push($(this).attr("id").replace("usrID_", ""));
                }
            });
            if (CombineIDs.length > 0) {
                $("[name=\"massActionIDs\"]").val(CombineIDs.join(","));
            } else {
                return;
            }
        }
        FromMassAction = true;
        $("#searchForm").submit();
    });

    $(".insertSearch").click(function () {
        var ThisVars = $(this).attr("class").split(" ");
        var Search = {string: "", type: ""};
        for (var elIdx in ThisVars) {
            if (ThisVars[elIdx].indexOf("string_") === 0) {
                Search.string = ThisVars[elIdx].replace("string_", "");
            } else if (ThisVars[elIdx].indexOf("type_") === 0) {
                Search.type = ThisVars[elIdx].replace("type_", "");
            }
        }
        $("[name=\"search_user\"]").val(Search.string);
        $("#SBy").val(Search.type);
        $("#searchForm").submit();
        return false;
    });
});
