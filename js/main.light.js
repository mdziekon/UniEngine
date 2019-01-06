function createCookie (name, value, days) {
    var expires;
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 86400000));
        expires = "; expires=" + date.toGMTString();
    } else {
        expires = "";
    }
    document.cookie = name + "=" + value + expires + "; path=/";
}
createCookie("var_1124", screen.width + "_" + screen.height + "_" + screen.colorDepth, 1);
