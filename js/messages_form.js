/* globals $MaxLength_Text */

$(document).ready(function () {
    var CharCounter = $("#charCounter");
    var TextBox = $("#textBox");

    $("#thisForm").submit(function () {
        $("input[type=\"submit\"]", $(this)).prop("disabled", true);
    });
    TextBox.keydown(function () {
        var ThisLength = $(this).val().length;
        if (ThisLength > $MaxLength_Text) {
            $(this).val($(this).val().substr(0, $MaxLength_Text));
            ThisLength = $MaxLength_Text;
        }
        CharCounter.html(ThisLength);
    }).change(function () {
        $(this).keydown();
    }).keyup(function () {
        $(this).keydown();
    });
    $("#thisReset").click(function () {
        TextBox.val("").keydown();
    });

    TextBox.keydown();
});
