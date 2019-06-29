/* globals YourNickname, JSLang, ErrorMsg, JSLang_Errors, LastSeenID, ServerStamp, UserAuth, RoomID */

// Initialize
var AjaxGet = false;
var GetTimeout = false;
var MsgBox = false;
var OnlineUsers = false;
var OnlineCount = false;
var LeftMenu_Messages = false;
var OldOnlineArray = [];
var OriginalTitle = "";
var TabActive = true;
var GetError_FatalOccured = false;
var NewMsgCount = 0;
var LastSeenID_Used = false;
var ReconnectCounter = 0;
var MaxReconnectCounts = 3;
var TitleFlashState = 1;
var UserMentioned = false;
var Messages = {
    users: {},
    msgCount: 0,
    firstID: 0,
    lastID: 0,
    lastGet: 0
};
var LastActivity_Time = 0;
var LastActivity_Type = 0;
var GetTimeout_MinVal = 2500;
var GetTimeout_InitVal = 5000;
var GetTimeout_MaxVal = 30000;
var GetTimeout_IdleStart = 60000;
var GetTimeout_Current = GetTimeout_InitVal;
var GetTimeout_AddToIdle_Val = 1000;
var GetTimeout_AddToActive_Val = 500;
var AjaxSettings = { timeout: 4000 };

var local_LastSeenID;

// TPL System
var TPL_NicknameReplace = "<b class=\"skyblue\">$1</b>";

// Intervals & Timeouts
var ReconnectInterval = false;
var ErrorFadeOutTimeout = false;
var SettingsInterval = false;
var TitleFlashInterval = false;

// Local script vars
var ShoutBoxTable;
var SendButton;
var ErrorBox;
var ActivityBox;
var SettingsBox;
var YourNickname_RegExp;
var YourNicknameReplace_RegExp;

// Functions
// > Sanitize some data
function preg_quote (str, delimiter) {
    //  discuss at: http://locutus.io/php/preg_quote/
    // original by: booeyOH
    // improved by: Ates Goral (http://magnetiq.com)
    // improved by: Kevin van Zonneveld (http://kvz.io)
    // improved by: Brett Zamir (http://brett-zamir.me)
    // bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
    //   example 1: preg_quote("$40")
    //   returns 1: '\\$40'
    //   example 2: preg_quote("*RRRING* Hello?")
    //   returns 2: '\\*RRRING\\* Hello\\?'
    //   example 3: preg_quote("\\.+*?[^]$(){}=!<>|:")
    //   returns 3: '\\\\\\.\\+\\*\\?\\[\\^\\]\\$\\(\\)\\{\\}\\=\\!\\<\\>\\|\\:'

    return (str + "").replace(
        new RegExp("[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\" + (delimiter || "") + "-]", "g"),
        "\\$&"
    );
}
// > Make pretty Time
function padTime (str) {
    str = "" + str;

    if (str.length == 1) {
        return "0" + str;
    } else if (str.length == 0) {
        return "00";
    }
    return str;
}
// > Parse given Data and create HTML Message
function createMessage (MessageData, curLastSeenID) {
    var MsgOpt = [];
    var AddLastMarker = "";
    var MsgDate = new Date((MessageData.d + ServerStamp) * 1000);

    if (UserAuth >= 90) {
        MsgOpt.push("<a class=\"delMsg\" href=\"#\"></a>");
        MsgOpt.push("<a class=\"edit\" href=\"chat_edit.php?mode=" + MessageData.id + "\"></a>");
    } else {
        MsgOpt.push("<a class=\"reportMsg\" href=\"#\" title=\"" + JSLang["ToolTip_ReportMsg"] + "\"></a>");
    }

    if (typeof curLastSeenID !== "undefined" && LastSeenID_Used === false) {
        if (MessageData.id == curLastSeenID) {
            AddLastMarker = " lastSeen";
            LastSeenID_Used = true;
        }
    }

    if (MsgOpt.length > 0) {
        MsgOpt = "<th valign=\"top\">" + MsgOpt.join("&nbsp;") + "</th>";
    } else {
        MsgOpt = "";
    }

    MsgDate = (
        padTime(MsgDate.getHours()) + ":" +
        padTime(MsgDate.getMinutes()) + ", " +
        padTime(MsgDate.getDate()) + "." +
        padTime((MsgDate.getMonth() + 1)) + "." + MsgDate.getFullYear()
    );

    return (
        "<tr id=\"sbmsg_" + MessageData.id + "\" class=\"doFadeIn " + AddLastMarker + "\">" +
            "<th class=\"usr aright\" valign=\"top\" nowrap>" +
                "<a href=\"#\" class=\"usrRef usrColor_" + Messages.users[MessageData.u].c + "\" title=\"" + JSLang["usrRef"] + "\">" +
                Messages.users[MessageData.u].n +
                "</a>" +
                " " +
                "<a href=\"profile.php?uid=" + MessageData.u + "\" class=\"usrColor_" + Messages.users[MessageData.u].c + "\" target=\"_blank\" title=\"" + JSLang["profile"] + "\">#</a>" +
            "</th>" +
            "<th class=\"msg aleft\">" +
                MessageData.t +
            "</th>" +
            "<th class=\"dt aright\" valign=\"top\" nowrap>" +
                "(" + MsgDate + ")" +
            "</th>" +
            MsgOpt +
        "</tr>"
    );
}
// > Add Reference
function AddReference (Text) {
    MsgBox.val(MsgBox.val() + Text + ": ").focus();
}
// > Get Data & Parse Messages
function showMessage () {
    var ReqParam = null;
    var ReCalc_FirstID = false;
    var ReCalc_LastID = false;
    var UpdateLastGetTime = false;

    if (Messages.init === false) {
        ReqParam = {
            init: true,
            rid: RoomID
        };
    } else {
        ReqParam = {rid: RoomID, fID: Messages.firstID, lID: Messages.lastID, lGet: Messages.lastGet, lCnt: Messages.msgCount};
    }

    ActivityBox.show(0);

    AjaxGet = $.get("ajax/chat.msg.php", ReqParam)
        .complete(function (reqResponse, reqStatus) {
            ActivityBox.hide(0);
            if (reqStatus == "success") {
                // ::: On Success :::
                var InAppError = false;

                // Clear last ErrorData
                if (ReconnectCounter > 0 || GetError_FatalOccured === true) {
                    GetError_FatalOccured = false;
                    ReconnectCounter = 0;
                    clearInterval(ReconnectInterval);
                    UnThrowError(true, true);
                }

                // --- Parse Reponse ---
                var Response = {};
                if (reqResponse.responseText != "") {
                    Response = $.parseJSON(reqResponse.responseText);
                }

                // Handle App (PHP) ErrorCodes
                if (Response.Err != null) {
                    InAppError = true;
                    ThrowError(JSLang_Errors["getErr_" + Response.Err], true, true, true);
                }

                // OnlineBox (String) Handler
                if (Response.onl != null) {
                    var NewOnlineUsers = [];
                    var SplitText = "";
                    var ThisOnlineArray = [];
                    for (var i in Response.onl) {
                        SplitText = Response.onl[i].split("|");
                        NewOnlineUsers.push(
                            "<a href=\"#\" class=\"usrRef usrColor_" + SplitText[2] + " " + ((SplitText[3] != null && SplitText[3] == 1) ? "usrInv" : "") + "\" title=\"" + JSLang["usrRef"] + "\">" + SplitText[1] + "</a> <a href=\"profile.php?uid=" + SplitText[0] + "\" class=\"usrColor_" + SplitText[2] + "\" target=\"_blank\" title=\"" + JSLang["profile"] + "\">#</a>"
                        );
                        ThisOnlineArray.push(SplitText[0]);
                    }
                    OnlineUsers.html(", " + NewOnlineUsers.join(", "));

                    // Reset LastActivityTime (Getter timeouts) if it's necessary
                    if (Messages.init !== false) {
                        var FoundNewUser = false;
                        for (var Index in ThisOnlineArray) {
                            if ($.inArray(ThisOnlineArray[Index], OldOnlineArray) === -1) {
                                FoundNewUser = true;
                                break;
                            }
                        }
                        if (FoundNewUser === true) {
                            LastActivity_Time = (new Date()).getTime();
                            LastActivity_Type = 2;
                        }
                    }

                    OldOnlineArray = ThisOnlineArray;
                } else {
                    OnlineUsers.html("");
                    OldOnlineArray = [];
                }

                // OnlineBox (Counter) Handler
                if (Response.onlCnt == null) {
                    Response.onlCnt = 0;
                }
                OnlineCount.html(parseInt(Response.onlCnt, 10) + 1);

                // Messages Handler
                if (InAppError !== true) {
                    if (Response.cmd == "delall") {
                        Messages = {
                            users: {},
                            msgCount: 0,
                            firstID: 0,
                            lastID: 0,
                            lastGet: 0
                        };
                        ShoutBoxTable.html("");
                    } else {
                        // :: Parse UsersArray
                        if (Response.usr != null) {
                            UpdateLastGetTime = true;
                            for (var UsrIdx in Response.usr) {
                                Messages.users[UsrIdx] = Response.usr[UsrIdx];
                            }
                        }
                        // :: Parse Message Edits
                        if (Response.edit != null) {
                            UpdateLastGetTime = true;
                            for (var EditMsgIdx in Response.edit) {
                                ShoutBoxTable.find("#sbmsg_" + EditMsgIdx + " > .msg").html(Response.edit[EditMsgIdx].t);
                            }
                        }
                        // :: Parse Message Deletions
                        if (Response.del != null) {
                            UpdateLastGetTime = true;
                            var DelElement = false;
                            for (var DelMsgIdx in Response.del) {
                                DelElement = ShoutBoxTable.find("#sbmsg_" + Response.del[DelMsgIdx]);
                                if (DelElement.length > 0) {
                                    Messages.msgCount -= 1;
                                    DelElement.remove();
                                    if (Response.del[DelMsgIdx] == Messages.lastID) {
                                        ReCalc_LastID = true;
                                    } else if (Response.del[DelMsgIdx] == Messages.firstID) {
                                        ReCalc_FirstID = true;
                                    }
                                }
                            }
                        }
                        // :: Parse New Messages
                        if (Response.newm != null) {
                            LastActivity_Time = (new Date()).getTime();
                            UpdateLastGetTime = true;

                            var NewMsgIdx = 0;
                            var msgID = 0;

                            // Currently, we don't have any Messages Loaded
                            if (Messages.msgCount == 0) {
                                var CombineMessages = "";
                                for (NewMsgIdx in Response.newm) {
                                    msgID = Response.newm[NewMsgIdx].id;
                                    if (Messages.firstID > msgID || Messages.firstID == 0) {
                                        Messages.firstID = msgID;
                                    }
                                    if (Messages.lastID < msgID) {
                                        Messages.lastID = msgID;
                                    }
                                    CombineMessages += createMessage(Response.newm[NewMsgIdx], local_LastSeenID);
                                    Messages.msgCount += 1;
                                }
                                ShoutBoxTable.fadeTo(0, 0.001);
                                ShoutBoxTable.append(CombineMessages);
                                $(".doFadeIn").removeClass("doFadeIn");
                                ShoutBoxTable.fadeTo(350, 1);
                            } else {
                                // We already have Messages Loaded
                                LastActivity_Type = 1;
                                var ToAppend = "";
                                var ToPrepend = "";
                                for (NewMsgIdx in Response.newm) {
                                    msgID = Response.newm[NewMsgIdx].id;
                                    if (Messages.firstID > msgID) {
                                        Messages.firstID = msgID;
                                        ToAppend += createMessage(Response.newm[NewMsgIdx]);
                                    } else {
                                        if (Messages.lastID < msgID) {
                                            Messages.lastID = msgID;
                                        }
                                        if (Response.newm[NewMsgIdx].t.search(YourNickname_RegExp) !== -1) {
                                            Response.newm[NewMsgIdx].t = Response.newm[NewMsgIdx].t.replace(YourNicknameReplace_RegExp, TPL_NicknameReplace);
                                            UserMentioned = true;
                                        }
                                        if (TabActive === false) {
                                            NewMsgCount += 1;
                                        } else {
                                            UserMentioned = false;
                                        }
                                        ToPrepend += createMessage(Response.newm[NewMsgIdx]);
                                    }
                                    Messages.msgCount += 1;
                                }

                                if (TabActive === false) {
                                    var NewTitle = OriginalTitle + " (" + NewMsgCount + ")";
                                    document.title = NewTitle;
                                    if (UserMentioned === true) {
                                        if (TitleFlashInterval !== false) {
                                            clearInterval(TitleFlashInterval);
                                        }
                                        TitleFlashState = 1;
                                        TitleFlashInterval = setInterval(function () {
                                            if (TitleFlashState === 1) {
                                                document.title = JSLang["mentioned"];
                                                TitleFlashState = 2;
                                            } else {
                                                document.title = NewTitle;
                                                TitleFlashState = 1;
                                            }
                                        }, 1000);
                                    }
                                }

                                if (ToAppend != "") {
                                    ShoutBoxTable.append(ToAppend);
                                    ToAppend = "";
                                }
                                if (ToPrepend != "") {
                                    ShoutBoxTable.prepend(ToPrepend);
                                    ToPrepend = "";
                                }
                                $(".doFadeIn").fadeTo(350, 1).removeClass("doFadeIn");
                            }
                        }
                        // :: Finishing Work
                        if (UpdateLastGetTime === true) {
                            Messages.lastGet = parseInt((new Date()).getTime() / 1000, 10) - ServerStamp;
                        }

                        // :: CleanUp Work
                        if (Messages.msgCount == 0) {
                            Messages.firstID = 0;
                            Messages.lastID = 0;
                        } else {
                            if (Messages.msgCount == 1) {
                                if (ReCalc_LastID === true) {
                                    Messages.lastID = parseInt(ShoutBoxTable.find("tr:first").attr("id").replace("sbmsg_", ""), 10);
                                }
                                Messages.firstID = Messages.lastID;
                            } else {
                                if (ReCalc_LastID === true) {
                                    Messages.lastID = parseInt(ShoutBoxTable.find("tr:first").attr("id").replace("sbmsg_", ""), 10);
                                }
                                if (ReCalc_FirstID === true) {
                                    Messages.firstID = parseInt(ShoutBoxTable.find("tr:last").attr("id").replace("sbmsg_", ""), 10);
                                }
                            }
                        }
                    }
                }

                // LeftMenu Update Handler
                if (Response.lmMC != null) {
                    var LeftMenu_MessagesCount = LeftMenu_Messages.children("#lm_msgc");
                    if (LeftMenu_MessagesCount.length > 0) {
                        LeftMenu_MessagesCount.html("(" + Response.lmMC + ")");
                    } else {
                        LeftMenu_Messages.addClass("orange").append("<b id=\"lm_msgc\">(" + Response.lmMC + ")</b>");
                    }
                } else {
                    LeftMenu_Messages.children("#lm_msgc").remove().end().removeClass("orange");
                }

                // GetTimeout setting
                if (LastActivity_Type > 0) {
                    if (LastActivity_Type == 1) {
                        GetTimeout_Current = GetTimeout_MinVal;
                    } else {
                        GetTimeout_Current = GetTimeout_InitVal;
                    }
                    LastActivity_Type = 0;
                } else {
                    if (((new Date()).getTime() - LastActivity_Time) >= GetTimeout_IdleStart) {
                        if (GetTimeout_Current < GetTimeout_MaxVal) {
                            GetTimeout_Current += GetTimeout_AddToIdle_Val;
                        }
                    } else {
                        if (GetTimeout_Current < GetTimeout_InitVal) {
                            GetTimeout_Current += GetTimeout_AddToActive_Val;
                        }
                    }
                }
                if (InAppError !== true) {
                    GetTimeout = setTimeout(showMessage, GetTimeout_Current);
                }
            } else {
                // ::: On Error :::
                if (reqStatus != "abort" && reqStatus != "notmodified") {
                    if (ReconnectCounter == 0) {
                        clearTimeout(ErrorFadeOutTimeout);
                        ThrowError("", true, true, true);
                    }

                    clearInterval(ReconnectInterval);
                    if (ReconnectCounter < MaxReconnectCounts) {
                        GetError_FatalOccured = false;
                        ReconnectCounter += 1;
                        ErrorBox.html(ErrorMsg["getError"].replace("{x}", MaxReconnectCounts - ReconnectCounter + 1));
                        ReconnectInterval = setTimeout(showMessage, 5000);
                    } else {
                        GetError_FatalOccured = true;
                        ErrorBox.html(ErrorMsg["getErrorFatal"]);
                    }
                }
            }
        });
}
// > Throw an Error Message in Box
function ThrowError (ThisErrorMsg, FadeChat, LockControl, NoUnThrow) {
    if (typeof ThisErrorMsg === "undefined") {
        ThisErrorMsg = "&nbsp;";
    }
    if (typeof FadeChat === "undefined") {
        FadeChat = false;
    }
    if (typeof LockControl === "undefined") {
        LockControl = false;
    }
    if (typeof NoUnThrow === "undefined") {
        NoUnThrow = false;
    }

    if (FadeChat === true) {
        ShoutBoxTable.fadeTo(400, 0.5);
    }
    if (LockControl === true) {
        SendButton.attr("disabled", true);
        MsgBox.attr("disabled", true);
    }

    ErrorBox.html(ThisErrorMsg).fadeIn(
        400,
        function () {
            if (NoUnThrow === false) {
                ErrorFadeOutTimeout = setTimeout(function () {
                    UnThrowError(FadeChat, LockControl);
                }, 1500);
            }
        }
    );
}
// > Destroy Error Box
function UnThrowError (UnFadeChat, UnLockControl) {
    ErrorBox.fadeOut(400);
    if (UnFadeChat === true) {
        ShoutBoxTable.fadeTo(400, 1);
    }
    if (UnLockControl === true) {
        SendButton.removeAttr("disabled");
        MsgBox.removeAttr("disabled");
    }
}

// > Abort request
function AjaxAbort () {
    if (typeof AjaxGet.readyState != "undefined") {
        if (AjaxGet.readyState != 4) {
            AjaxGet.abort();
        }
    }
}

// Main Part
$(document).ready(function () {
    local_LastSeenID = LastSeenID;

    // Initialization
    $.ajaxSetup(AjaxSettings);

    OriginalTitle = document.title;
    var YourNicknameEscaped = preg_quote(YourNickname);
    YourNickname_RegExp = new RegExp(YourNicknameEscaped, "gi");
    YourNicknameReplace_RegExp = new RegExp("(" + YourNicknameEscaped + ")", "gi");

    MsgBox = $("#msgText");
    ShoutBoxTable = $("#sbTable");
    SendButton = $("#msgSend");
    OnlineUsers = $("#onlineUsers");
    OnlineCount = $("#onlineCount");
    ErrorBox = $("#errorBox");
    ActivityBox = $("#activityBox");
    SettingsBox = $("#set_Loading");
    LeftMenu_Messages = $("#lm_msg");

    showMessage();

    // SendButton Click Handler
    MsgBox.keypress(function (event) {
        if (event.keyCode == 13 && !event.shiftKey) {
            SendButton.click();
        }
    });

    // MessageDeletion Handler
    ShoutBoxTable
        .on("click", ".delMsg", function () {
            clearTimeout(GetTimeout);
            var ThisID = $(this).parent().parent().attr("id").replace("sbmsg_", "");
            ActivityBox.show(0);

            AjaxAbort();
            $.get("ajax/chat.del.php", {id: ThisID})
                .success(function (data) {
                    ActivityBox.hide(0);
                    if (data != 1) {
                        var FoundError;
                        var NoUnThrow;

                        if (data != 5) {
                            FoundError = ErrorMsg["jQueryAjax_delErr_" + data].replace("{$id}", ThisID);
                            NoUnThrow = false;
                        } else {
                            FoundError = ErrorMsg["jQueryAjax_notLogged"];
                            NoUnThrow = true;
                        }

                        ThrowError(FoundError, false, false, NoUnThrow);
                    }

                    if (data != 5) {
                        showMessage();
                    }
                })
                .error(function () {
                    ActivityBox.hide(0);
                    showMessage();
                });

            return false;
        })
        .on("click", ".reportMsg", function () {
            var ThisID = $(this).parent().parent().attr("id").replace("sbmsg_", "");
            var ThisUID = $(this).parent().parent().find("th.usr > a:not(.usrRef)").attr("href").replace("profile.php?uid=", "");
            window.location.href = "report.php?type=9&uid=" + ThisUID + "&eid=" + ThisID;

            return false;
        });

    // UserReference Adder Handler
    $("#shoutbox").on("click", ".usrRef", function (event) {
        event.preventDefault();
        AddReference($(this).html());
    });

    // FatalError Reload Handler
    ErrorBox.on("click", "#chatReload", function () {
        if (GetError_FatalOccured !== true) {
            return false;
        }
        ReconnectCounter = 0;
        showMessage();
        return false;
    });

    // SendButton Handler
    SendButton.click(function () {
        if (MsgBox.val() != "") {
            clearTimeout(GetTimeout);
            MsgBox.attr("disabled", true);
            SendButton.attr("disabled", true);
            ActivityBox.show(0);

            AjaxAbort();
            $.post("ajax/chat.add.php", {rid: RoomID, msg: MsgBox.val()})
                .success(function (data) {
                    ActivityBox.hide(0);
                    if (data == 1) {
                        MsgBox.val("").removeAttr("disabled");
                        SendButton.removeAttr("disabled");
                        showMessage();
                    } else {
                        var FoundError;
                        var NoUnThrow;

                        if (data != 4) {
                            FoundError = ErrorMsg["jQueryAjax_postErr_" + data];
                            NoUnThrow = false;
                        } else {
                            FoundError = ErrorMsg["jQueryAjax_notLogged"];
                            NoUnThrow = true;
                        }
                        ThrowError(FoundError, true, true, NoUnThrow);
                        GetTimeout = setTimeout(showMessage, 2500);
                    }
                })
                .error(function () {
                    ActivityBox.hide(0);
                    ThrowError(ErrorMsg["jQueryAjax_postError"], true, true);
                    GetTimeout = setTimeout(showMessage, 2500);
                });
        }
    });

    // Settings Handler
    $(".setChg").change(function () {
        if (SettingsInterval !== false) {
            clearTimeout(SettingsInterval);
        }
        SettingsBox.fadeIn(0).attr("src", "images/ajax-loader.gif");
        var ThisPost = {};
        var OnSuccess = function () {
            return;
        };
        var RevertChange = false;
        var ThisElement = $(this);
        if ($(this).attr("type") == "checkbox") {
            ThisPost[$(this).attr("name")] = $(this).is(":checked");
            RevertChange = function () {
                ThisElement.prop("checked", !ThisElement.is(":checked"));
            };

            if (ThisPost["setChg_1"] != null) {
                if (ThisPost["setChg_1"] === true) {
                    OnSuccess = function () {
                        $("#onlineBox_you").addClass("usrInv");
                    };
                } else if (ThisPost["setChg_1"] === false) {
                    OnSuccess = function () {
                        $("#onlineBox_you").removeClass("usrInv");
                    };
                }
            }
        } else {
            ThisPost[$(this).attr("name")] = $(this).val();
            RevertChange = function () {
                ThisElement.val("");
            };
        }
        $.post("ajax/chat.settings.php", ThisPost)
            .success(function (data) {
                if (data == 1) {
                    SettingsBox.attr("src", "images/tick.green.png");
                    OnSuccess();
                } else {
                    SettingsBox.attr("src", "images/tick.red.png");
                    RevertChange();
                }
                SettingsInterval = setTimeout(function () {
                    SettingsBox.fadeOut(500);
                }, 1000);
            })
            .error(function () {
                SettingsBox.attr("src", "images/delete.png");
                SettingsInterval = setTimeout(function () {
                    SettingsBox.fadeOut(500);
                }, 1000);
            });
    });

    // WindowState (Away/Active) Handler
    $(window)
        .blur(function () {
            // User is going Offline In-Tab
            TabActive = false;
            if (local_LastSeenID < Messages.lastID) {
                $("#sbmsg_" + local_LastSeenID).removeClass("lastSeen");
                $("#sbmsg_" + Messages.lastID).addClass("lastSeen");
                local_LastSeenID = Messages.lastID;
            }
        })
        .focus(function () {
            // User has returned to Online In-Tab
            if (TitleFlashInterval !== false) {
                clearInterval(TitleFlashInterval);
            }
            UserMentioned = false;
            TabActive = true;
            NewMsgCount = 0;
            document.title = OriginalTitle;
        });
});
