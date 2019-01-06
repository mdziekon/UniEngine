/* globals AutoExpandAmp, AutoExpandArray, Locale */

var Intervals = new Array();
var LastVals = new Array();
var Temp = "";
$(document).ready(function () {
    // Functions Initialization
    function colorButton (Button) {
        Button.css("color", "rgb(102,255,51)");

        Intervals[Button.attr("value")] = setInterval(function () {
            if (Button.css("color") == "rgb(255, 255, 255)") {
                clearInterval(Intervals[Button.attr("value")]);
            } else {
                var Explode = Button.css("color");
                Explode = Explode.replace("rgb(", "");
                Explode = Explode.replace(")", "");
                Explode = Explode.replace(" ", "");
                Explode = Explode.split(",");
                Button.css("color", "rgb(" + (parseInt(Explode[0]) + 3) + ",255," + (parseInt(Explode[2]) + 4) + ")");
            }
        }, 25);
    }
    // Manual Expands & Collapses

    $("#expAllArr").click(function () {
        colorButton($(this));

        $(".expandArr").each(function () {
            if (!$(this).parent().prev().hasClass("arrExp")) {
                $(this).click();
            }
        });
    });
    $("#expAllAmp").click(function () {
        colorButton($(this));

        $(".expandAmp").each(function () {
            if (!$(this).parent().prev().hasClass("ampExp")) {
                $(this).click();
            }
        });
    });
    $("#colAllArr").click(function () {
        colorButton($(this));

        $(".expandArr").each(function () {
            if ($(this).parent().prev().hasClass("arrExp")) {
                $(this).click();
            }
        });
    });
    $("#colAllAmp").click(function () {
        colorButton($(this));

        $(".expandAmp").each(function () {
            if ($(this).parent().prev().hasClass("ampExp")) {
                $(this).click();
            }
        });
    });

    $(".expandArr").click(function () {
        var ThisElement = $(this).parent().prev();
        if (!ThisElement.hasClass("arrExp")) {
            ThisElement.addClass("arrExp");
            $(this).html(Locale[1]);
            $(".post_bo, .post_bc", ThisElement).prepend("<b class=\"br_t\"><br/></b>");
            if (!ThisElement.hasClass("ampExp")) {
                $(".post_bo, .post_bc", ThisElement).append("<b class=\"br_b\"><br/></b>");
                $(".p_b", ThisElement).html("<br/>");
            }
            $(".pos_sel", ThisElement).addClass("pos");
        } else {
            $(this).html(Locale[0]);
            ThisElement.removeClass("arrExp");

            $(".br_t, .br_b", ThisElement).remove();
            if (ThisElement.hasClass("ampExp")) {
                $(".post_bo", ThisElement).append("<b class=\"br_b\"><br/></b>");
            }
            $(".p_b", ThisElement).html("");
            $(".pos_sel", ThisElement).removeClass("pos");
        }
        return false;
    });
    $(".expandAmp").click(function () {
        var ThisElement = $(this).parent().prev();
        if (!ThisElement.hasClass("ampExp")) {
            ThisElement.addClass("ampExp");
            $(this).html(Locale[3]);

            $(".p_e, .p_a", ThisElement).html("<br/>");
            $(".p_b", ThisElement).each(function () {
                $(this).html("");
            });

            if (!ThisElement.hasClass("arrExp")) {
                $(".post_bo", ThisElement).append("<b class=\"br_b\"><br/></b>");
            } else {
                $(".post_bc > .br_b", ThisElement).remove();
            }
        } else {
            $(this).html(Locale[2]);
            ThisElement.removeClass("ampExp");

            $(".p_e", ThisElement).html("");
            $(".p_a", ThisElement).html("&amp;");
            if (ThisElement.hasClass("arrExp")) {
                $(".p_b", ThisElement).html("<br/>");
                $(".post_bc", ThisElement).append("<b class=\"br_b\"><br/></b>");
            } else {
                $(".br_t, .br_b", ThisElement).remove();
            }
        }
        return false;
    });

    // AutoExpands
    if (AutoExpandAmp) {
        $("#expAllAmp").click();
    }
    if (AutoExpandArray) {
        $("#expAllArr").click();
    } else {
        $(".p_b").each(function () {
            $(this).html("");
        });
        $(".pos_sel").each(function () {
            $(this).removeClass("pos");
        });
    }

    // Display
    $("input[type=\"text\"]").focus(function () {
        Temp = $(this).attr("name");
        LastVals[Temp] = $(this).val();
        $(this).val("");
    });
    $("input[type=\"text\"]").blur(function () {
        if ($(this).val() == "") {
            Temp = $(this).attr("name");
            $(this).val(LastVals[Temp]);
        }
    });
    $("input[type=\"reset\"]").click(function () {
        var loc = new String(window.location);
        window.location = loc.replace(/(&amp;|&){1}page=[0-9]+/, "");
    });
    $(".get").hover(function () {
        $(this).addClass("get_hover_name");
        $(this).children().each(function () {
            if ($(this).hasClass("gv")) {
                $(this).addClass("get_hover_value");
            } else if ($(this).hasClass("ge")) {
                $(this).addClass("get_hover_eq");
            }
        });
    }, function () {
        $(this).removeClass("get_hover_name");
        $(this).children().each(function () {
            if ($(this).hasClass("gv")) {
                $(this).removeClass("get_hover_value");
            } else if ($(this).hasClass("ge")) {
                $(this).removeClass("get_hover_eq");
            }
        });
    });
    $(".post_a").hover(function () {
        $(this).children().each(function () {
            if ($(this).hasClass("post_a_")) {
                $(this).addClass("post_hover_a_");
            }
        });
    }, function () {
        $(this).children().each(function () {
            if ($(this).hasClass("post_a_")) {
                $(this).removeClass("post_hover_a_");
            }
        });
    });

    // Form Control
    $(".pagin").click(function () {
        if ($("#thisForm").attr("action").indexOf("page") === -1) {
            $("#thisForm").attr("action", $("#thisForm").attr("action") + "&page=" + $(this).attr("name").replace("goto_", "")).submit();
        } else {
            $("#thisForm").attr("action", $("#thisForm").attr("action").replace(/page=[0-9]+/gi,"page=" + $(this).attr("name").replace("goto_", ""))).submit();
        }
    });
    $(".doFilter").click(function () {
        $("[name=\"filter\"]").val("on");
    });
    $(".doCleanFilter").click(function () {
        $("[name=\"filter\"]").val("off");
    });

    $(".pagebut").hover(function () {
        $(this).addClass("hover");
    }, function () {
        $(this).removeClass("hover");
    });

    $(".activ_filter_time").click(function () {
        $("[name=\"filter_time\"]").attr("checked", "checked");
    });
    $(".activ_filter_place").click(function () {
        $("[name=\"filter_place\"]").attr("checked", "checked");
    });

    $(".rN").hover(function () {
        $(this).children().addClass("rowHover");
    }, function () {
        $(".rowHover").removeClass("rowHover");
    });

    $(".rM1, .rM2").hover(function () {
        $(this).children().addClass("rowHover");
        if ($(this).hasClass("rM1")) {
            $(this).next().children().addClass("rowHover");
        } else {
            $(this).prev().children().addClass("rowHover");
        }
    }, function () {
        $(".rowHover").removeClass("rowHover");
    });

    $(".strFold").click(function () {
        if (!$(this).hasClass("isFolded")) {
            $(this).addClass("isFolded");
            $(".doUnf", $(this)).hide(0);
            $(".folded", $(this)).show(0);
        } else {
            $(this).removeClass("isFolded");
            $(".doUnf", $(this)).show(0);
            $(".folded", $(this)).hide(0);
        }
    });
});
