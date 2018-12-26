/* globals JSLang */

$(document).ready(function () {
    var qtipObj = {
        style: {
            classes: "tiptip_content"
        },
        position: {
            my: "right middle",
            at: "left middle"
        }
    };
    $(".obligatory").qtip($.extend(qtipObj, {content: JSLang["Tip_Obligatory"]}));
    $(".locked.voted").qtip($.extend(qtipObj, {content: JSLang["Tip_LockedVoted"]}));
    $(".locked:not(.voted)").qtip($.extend(qtipObj, {content: JSLang["Tip_Locked"]}));
    $(".voted:not(.locked)").qtip($.extend(qtipObj, {content: JSLang["Tip_Voted"]}));
});
