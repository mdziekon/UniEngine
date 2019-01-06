/* globals jsLang, useQuickRes */

$(document).ready(function () {
    $(".planet").tipTip({delay: 0, edgeOffset: 8, content: jsLang["fl_coordplanet"]});
    $(".moon").tipTip({delay: 0, edgeOffset: 8, content: jsLang["fl_coordmoon"]});
    $(".debris").tipTip({delay: 0, edgeOffset: 8, content: jsLang["fl_coorddebris"]});

    if (useQuickRes === true) {
        $("select#planet").children().each(function () {
            $(this).val($(this).val() + "&quickres=1");
        });
    }
});
