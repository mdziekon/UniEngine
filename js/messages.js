/* globals JS_Lang, DynamicCode, SpyExpanded */
/* exported f */

function f (target_url, win_name) {
    var new_win = window.open(target_url,win_name,"resizable=yes,scrollbars=yes,menubar=no,toolbar=no,width=550,height=280,top=0,left=0");
    new_win.focus();
}
function conv (id) {
    var conv_win = window.open("converter.php?id=" + id, "convert_" + id, "resizable=yes,scrollbars=yes,menubar=no,toolbar=no,width=600,height=400,top=0,left=0");
    conv_win.focus();
}

var AjaxSettings = {timeout: 5000};

$(document).ready(function () {
    $.ajaxSetup(AjaxSettings);
    var DontCreateCompactIDs = false;
    var MsgCont = $("#msgCont");

    // ToolTips handler
    MsgCont.on("mouseover", ".hov", function () {
        if ($(this).data("hasTip") !== true) {
            var ThisChild = $(this).children("*").attr("class");
            if (ThisChild == "convert") {
                $(this).tipTip({delay: 0, edgeOffset: 8, content: JS_Lang["mess_convert_title"]});
            } else if (ThisChild == "delete") {
                $(this).tipTip({delay: 0, edgeOffset: 8, content: JS_Lang["mess_delete_single_title"]});
            } else if (ThisChild == "reply") {
                $(this).tipTip({delay: 0, edgeOffset: 8, content: JS_Lang["mess_reply_title"]});
            } else if (ThisChild == "reply2") {
                $(this).tipTip({delay: 0, edgeOffset: 8, content: JS_Lang["mess_replyally_title"]});
            } else if (ThisChild == "ignore") {
                $(this).tipTip({delay: 0, edgeOffset: 8, content: JS_Lang["mess_ignore_title"]});
            } else if (ThisChild == "report") {
                $(this).tipTip({delay: 0, edgeOffset: 8, content: JS_Lang["mess_report_e_title"]});
            } else if (ThisChild == "report2") {
                $(this).tipTip({delay: 0, edgeOffset: 8, maxWidth: "300px", content: JS_Lang["mess_report_title"]});
            }
            $(this).data("hasTip", true).trigger("mouseover");
        }
    });

    $(".selectAll").tipTip({delay: 0, content: JS_Lang["mess_selectall_title"], edgeOffset: 10});

    // Option click
    MsgCont.on("click", ".hov", function () {
        $(this).children("*").get(0).click();
    });

    // Select All handler
    $(".selectAll").click(function () {
        var isChecked = $(this).is(":checked");
        $(".eMark > input").attr("checked", isChecked);
        $(".selectAll").attr("checked", isChecked);
    });
    // Checkbox click handler
    MsgCont.on("click", ".eMark > input", function () {
        if (!$(this).is(":checked")) {
            $(".selectAll").attr("checked", false);
        } else {
            var AllChecked = true;
            $(".eMark > input").each(function () {
                if (!$(this).is(":checked")) {
                    AllChecked = false;
                    return false;
                }
            });
            if (AllChecked) {
                $(".selectAll").attr("checked", true);
            }
        }
    });
    // Convert button handler
    MsgCont.on("click", ".convert", function () {
        var GetID = $(this).attr("id").replace("cv_", "");
        conv(GetID);
        return false;
    });
    // Delete button handler
    MsgCont.on("click", ".delete", function () {
        DontCreateCompactIDs = true;
        var ThisID = $(this).parents("tr").prev("tr").find("input[type=\"checkbox\"]").attr("name").replace("del", "");
        $("#delid").val(ThisID);
        $("[name^=\"del\"]:not([name=\"delid\"]), [name^=\"sm\"], [name=\"time\"]", $("#msg_form")).attr("name", "");
        $("#msg_form").submit();

        return false;
    });
    // Simulation button handler
    $(".spySim").click(function () {
        $("#" + $(this).attr("id").replace("spy_", "")).submit();
        return false;
    });
    // SpyReport handler
    $(".SpyExpand").click(function () {
        if ($(this).children("img").attr("src") == "images/collapse.png") {
            $(this).parents(".msgrow").find(".sth:not(.nohide)").hide(0);
            $(this).children("img").attr("src", "images/expand.png");
        } else {
            $(this).parents(".msgrow").find(".sth:not(.nohide)").show(0);
            $(this).children("img").attr("src", "images/collapse.png");
        }
    });

    // Threaded Conversation Handler
    $(".thAct").click(function () {
        if ($(this).data("isLocked") !== true) {
            var ThisID = $(this).attr("id").replace("thAct_", "");
            var ThisEl = $(this);
            ThisEl.data("isLocked", true);

            if (ThisEl.data("expanded") !== true) {
                ThisEl.children(".epd").hide(0, function () {
                    if (ThisEl.data("loaded") !== true) {
                        if (ThisEl.children(".load").length <= 0) {
                            ThisEl.append(DynamicCode["loading"]);
                        }
                        ThisEl.children(".load").show(0);
                        var ThisGetData = ThisEl.attr("class").replace("thAct ", "").split(" ");
                        var Excludes = ThisGetData[0].replace("exc_", "");
                        var MaxID = ThisGetData[1].replace("mid_", "");
                        var HasCat = ThisGetData[2].replace("c_", "");
                        var NoCat = 0;
                        if (Excludes != "") {
                            Excludes.split("_").join(",");
                        } else {
                            Excludes = "";
                        }
                        if (HasCat == 100) {
                            NoCat = 1;
                        }

                        $.get("ajax/messages.conversation.php", {tid: ThisID, exc: Excludes, mid: MaxID, nc: NoCat})
                            .complete(function (getResponse, getStatus) {
                                var ErrorOccured = false;
                                if (getStatus == "success") {
                                    var ParseResponse = $.parseJSON(getResponse.responseText);
                                    if (typeof ParseResponse.Err == "undefined") {
                                        $("#thID_" + ThisID).html(DynamicCode["sBR"] + ParseResponse.Code + DynamicCode["sBR"]).show(250);
                                        ThisEl.data("loaded", true);
                                        ThisEl.append(DynamicCode["collapse"]);
                                        ThisEl.children(".load").hide(0, function () {
                                            ThisEl.children(".cpe").show(0, function () {
                                                ThisEl.data("expanded", true);
                                                ThisEl.data("isLocked", false);
                                            });
                                        });
                                    } else {
                                        ErrorOccured = true;
                                    }
                                }
                                if (getStatus != "success" || ErrorOccured === true) {
                                    // An Error Occured!
                                    ThisEl.children(".load").hide(0, function () {
                                        if (ThisEl.children(".therr").length <= 0) {
                                            ThisEl.append(DynamicCode["error"]);
                                        }

                                        ThisEl.children(".therr").show(0);
                                        setTimeout(function () {
                                            ThisEl.children(".therr").animate({opacity: 0.0001}, 250, function () {
                                                $(this).css({opacity: 1}).hide(0);
                                                ThisEl.children(".epd").show(0).css({opacity: 0.0001}).animate({opacity: 1}, 250, function () {
                                                    ThisEl.data("isLocked", false);
                                                });
                                            });
                                        }, 1500);
                                    });
                                }
                            });
                    } else {
                        $("#thID_" + ThisID).show(250);
                        ThisEl.children(".cpe").show(0, function () {
                            ThisEl.data("expanded", true);
                            ThisEl.data("isLocked", false);
                        });
                    }
                });
            } else {
                $("#thID_" + ThisID).hide(250);
                ThisEl.children(".cpe").hide(0, function () {
                    ThisEl.children(".epd").show(0, function () {
                        ThisEl.data("expanded", false);
                        ThisEl.data("isLocked", false);
                    });
                });
            }
        }
    });

    // Form handler
    $(".delMsgSel").change(function () {
        $(".delMsgSel").val($(this).val());
    });
    $("#msg_form").submit(function () {
        var Return = true;
        var Command = $("[name=deletemessages]").val();

        if (Command == "deleteall") {
            Return = confirm(JS_Lang["Sure_WantDeleteAll"]);
        } else if (Command == "deleteallcat") {
            Return = confirm(JS_Lang["Sure_WantDeleteCat"]);
        }
        if (Return === true && DontCreateCompactIDs === false) {
            var GetID;
            var GetThisID;
            if (Command == "deleteunmarked") {
                GetID = [];
                GetThisID = 0;
                $("[name^=\"sm\"]", $(this)).each(function () {
                    GetThisID = new RegExp("sm([0-9]{1,})", "gi").exec($(this).attr("name"))[1];
                    if (!$("[name=\"del" + GetThisID + "\"]").is(":checked")) {
                        GetID.push(GetThisID);
                    }
                    $(this).attr("name", "");
                });
                $("[name^=\"del\"]:not([name=\"deletemessages\"]), [name=\"time\"]", $(this)).attr("name", "");
                $(this).prepend("<input type=\"hidden\" name=\"sm_all\" value=\"" + GetID.join(",") + "\"/>");
            } else if (Command == "deletemarked") {
                GetID = [];
                GetThisID = 0;
                $("[name^=\"del\"][type=\"checkbox\"]", $(this)).each(function () {
                    GetThisID = new RegExp("del([0-9]{1,})", "gi").exec($(this).attr("name"));
                    if (typeof GetThisID[1] != "undefined") {
                        if ($(this).is(":checked")) {
                            GetID.push(GetThisID[1]);
                        }
                    }
                    $(this).attr("name", "");
                });
                $("[name^=\"sm\"], [name=\"delid\"], [name=\"time\"]", $(this)).attr("name", "");
                $(this).prepend("<input type=\"hidden\" name=\"del_all\" value=\"" + GetID.join(",") + "\"/>");
            } else {
                $("[name^=\"del\"]:not([name=\"deletemessages\"]), [name^=\"sm\"]", $("#msg_form")).attr("name", "");
            }
        }
        return Return;
    });

    if (SpyExpanded === true) {
        $(".SpyExpand").children("img").attr("src", "images/collapse.png");
    } else {
        $(".SpyExpand").children("img").attr("src", "images/expand.png");
    }
});
