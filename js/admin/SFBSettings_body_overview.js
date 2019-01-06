/* globals JSLang */

$(document).ready(function () {
    $(".reason").find("img").each(function () {
        $(this).attr("src", "../" + $(this).attr("src"));
    });

    var qTipStyles = {classes:"tiptip_content"};
    var qTipPos = {my:"top center",at:"bottom center",adjust:{y:5}};
    $(".hPostEndTime").qtip({content: JSLang["SFB_Help_PostEndTime"], show:{delay: 500}, style: qTipStyles, position: qTipPos});
    $(".hEndTime").qtip({content: JSLang["SFB_Help_EndTime"], show:{delay: 500}, style: qTipStyles, position: qTipPos});
    $(".cancelLink").click(function () {
        return confirm(JSLang["SFB_Confirm_Cancel"]);
    });
});
