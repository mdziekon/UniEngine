/* globals JSLang, CurrentPage */

$(document).ready(function () {
    $(".delete.tipTitle").tipTip({delay: 0, maxWidth: "200px", content: JSLang["CMD_Delete_Title"]});
    $(".search.tipTitle").tipTip({delay: 0, maxWidth: "200px", content: JSLang["CMD_Show_Users_Title"]});
    $(".users.tipTitle").tipTip({delay: 0, maxWidth: "200px", content: JSLang["CMD_Show_MainUsers_Title"]});

    $(".pagin").click(function () {
        $(this).parent().attr("action", "?page=" + $(this).attr("name").replace("goto_", "")).submit();
    });
    $(".perPage").change(function () {
        var AddPageAction;
        var getPerPage = $(this).val();

        if (CurrentPage > 1) {
            AddPageAction = "page=" + CurrentPage + "&";
        } else {
            AddPageAction = "";
        }

        $(this).parent().parent().attr("action", "?" + AddPageAction + "pp=" + getPerPage).submit();
    });

    $("#CMD_DelAll").click(function () {
        return confirm(JSLang["CMD_DelAll"]);
    });
});
