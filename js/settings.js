/* globals OldResSort, TemplateData, OverrideTab, OldPass, VacationMiliseconds */

$(document).ready(function () {
    var local_OverrideTab = OverrideTab;

    var setCat = $(".setCat");
    var setCatLabel = $(".setCat > :first-child");
    var Cont = $("table.tableCont:not(.visible)");
    var FCPickers = $("#fc_pickers");
    var ColorPickers = new Object;
    var ColorPickerDivs = new Object;
    var HideTimeout = false;

    if ($(document.location.hash).length > 0) {
        local_OverrideTab = document.location.hash.replace("#Tab", "");
    }
    if (local_OverrideTab == "") {
        local_OverrideTab = "01";
    }

    // Tab Handler
    setCatLabel.hover(function () {
        $(this).parent().addClass("hover");
    }, function () {
        $(this).parent().removeClass("hover");
    });
    setCatLabel.click(function () {
        if (!$(this).parent().hasClass("active")) {
            var ThisTab = $(this).attr("id").replace("Tab", "");
            setCat.removeClass("active");
            $(this).parent().addClass("active");

            Cont.hide(0);
            $("#Cont" + ThisTab).show(0);
            document.location.hash = "Tab" + ThisTab;
            $(document).scrollTop(0);
        }
    });

    $("#Tab" + local_OverrideTab).click();

    if ($("[name=\"development_old\"]").is(":checked") === false) {
        $("[name=\"build_expandedview_use\"]").prop("disabled", true);
        $("#build_expandedviewinfo").css("text-decoration", "line-through");
    }
    $("[name=\"development_old\"]").click(function () {
        if ($(this).is(":checked")) {
            $("[name=\"build_expandedview_use\"]").prop("disabled", false);
            $("#build_expandedviewinfo").css("text-decoration", "");
        } else {
            $("[name=\"build_expandedview_use\"]").prop("disabled", true);
            $("#build_expandedviewinfo").css("text-decoration", "line-through");
        }
    });

    $(".help").tipTip({delay: 0, maxWidth: "300px", attribute: "title"});
    $("[name=\"give_oldpass\"]").keypress(function () {
        setTimeout(function () {
            if ($("[name=\"give_oldpass\"]").val() != "") {
                CompareVals(md5($("[name=\"give_oldpass\"]").val()), OldPass, "[name=\"give_oldpass\"]");
            } else {
                $("[name=\"give_oldpass\"]").css({"border-color": "#415680", "color": "#E6EBFB"});
            }
        }, 100);
    });
    $("[name=\"delete_confirm\"]").keypress(function () {
        setTimeout(function () {
            if ($("[name=\"delete_confirm\"]").val() != "") {
                CompareVals(md5($("[name=\"delete_confirm\"]").val()), OldPass, "[name=\"delete_confirm\"]");
            } else {
                $("[name=\"delete_confirm\"]").css({"border-color": "#415680", "color": "#E6EBFB"});
            }
        }, 100);
    });
    $("[name=\"give_newpass\"]").keypress(function () {
        setTimeout(function () {
            CompareInputs("[name=\"give_confirmpass\"]", "[name=\"give_newpass\"]");
        }, 100);
    });
    $("[name=\"give_newemail\"]").keypress(function () {
        setTimeout(function () {
            CompareInputs("[name=\"give_confirmemail\"]", "[name=\"give_newemail\"]");
        }, 100);
    });
    $("[name=\"give_confirmpass\"]").keypress(function () {
        setTimeout(function () {
            CompareInputs("[name=\"give_confirmpass\"]", "[name=\"give_newpass\"]");
        }, 100);
    });
    $("[name=\"give_confirmemail\"]").keypress(function () {
        setTimeout(function () {
            CompareInputs("[name=\"give_confirmemail\"]", "[name=\"give_newemail\"]");
        }, 100);
    });

    $(".saveType_DelIgnore").click(function () {
        $("[name=\"saveType\"]").val("delignore");
    });
    $(".saveType_Ignore").click(function () {
        $("[name=\"saveType\"]").val("ignore");
    });
    $("[name=\"ignore_username\"]").keydown(function (event) {
        if (event.which == "13") {
            $("[name=\"saveType\"]").val("ignore");
        }
    });
    $("input[type=\"submit\"]").click(function () {
        var AllowSubmit = true;
        var Highlight = false;
        var GoToMark = false;
        if (($(this).val() == TemplateData["SaveOnlyThis"] && $("[name=markActive]").val() == "Cont02") || $(this).val() == TemplateData["SaveAll"]) {
            if ($("[name=\"use_skin\"]").is(":checked") == false) {
                if (TemplateData["use_skin_check"] != "") {
                    AllowSubmit = confirm(TemplateData["AYS_WantNoSkin"]);
                    Highlight = "skin_use";
                    GoToMark = "Mark02";
                }
            } else {
                if (TemplateData["skin_path"] != $("[name=\"skin_path\"]").val()) {
                    GoToMark = "Mark02";
                    $.ajax(
                        {
                            async: false,
                            url: "./ajax/checkSkin.php",
                            type: "post",
                            data: {skin_path: $("[name=\"skin_path\"]").val()},
                            timeout: 1500,
                            success: function (data) {
                                if (data == 0) {
                                    alert(TemplateData["SetSkin_BadNetSkin"]);
                                    AllowSubmit = false;
                                    Highlight = "skin_url";
                                } else if (data == 1) {
                                    alert(TemplateData["SetSkin_BadLocSkin"]);
                                    AllowSubmit = false;
                                    Highlight = "skin_select";
                                } else if (data == 2) {
                                    AllowSubmit = true;
                                } else {
                                    AllowSubmit = confirm(TemplateData["SetSkin_AjaxError"]);
                                }
                            },
                            error: function () {
                                AllowSubmit = confirm(TemplateData["SetSkin_AjaxError"]);
                            }
                        });
                }
            }
        }
        if (AllowSubmit === false) {
            if (GoToMark !== false) {
                $("#" + GoToMark).click();
            }
            if (Highlight !== false) {
                $(".highlight_" + Highlight).effect("highlight", {"color": "orange"}, 1500);
            }
            return false;
        }
    });
    $("#skinSelector").change(function () {
        $("[name=\"skin_path\"]").val($("#skinSelector option:selected").val());
        $("[name=\"skin_path\"]").change();
    });
    $("[name=\"skin_path\"]").change(function () {
        if ($(this).val() != "") {
            $(this).data("lastVal", $(this).val());
            if (!$("[name=\"use_skin\"]").is(":checked")) {
                $("[name=\"use_skin\"]").prop("checked", true);
            }
        } else {
            if ($("[name=\"use_skin\"]").is(":checked")) {
                $("[name=\"use_skin\"]").prop("checked", false);
            }
        }
        $("#skinSelector").val($(this).val()).prop("selected", true);
    }).keyup(function () {
        $(this).change();
    });
    $("[name=\"use_skin\"]").click(function () {
        if ($(this).is(":checked")) {
            if ($("[name=\"skin_path\"]").val() == "") {
                if ($("[name=\"skin_path\"]").data("lastVal") !== undefined) {
                    $("[name=\"skin_path\"]").val($("[name=\"skin_path\"]").data("lastVal"));
                    $("#skinSelector").val($("[name=\"skin_path\"]").data("lastVal")).prop("selected", true);
                }
            }
        } else {
            if ($("[name=\"skin_path\"]").val() != "") {
                $("[name=\"skin_path\"]").data("lastVal", $("[name=\"skin_path\"]").val());
                $("[name=\"skin_path\"]").val("");
            }
            $("#skinSelector").val("").prop("selected", true);
        }
    });

    $("#ResSort").sortable(
        {
            opacity: 0.6,
            cursor: "move",
            update: function () {
                var ResSortArray = $(this).sortable("toArray");
                var NewSort = new Array;
                var ThisIndexInt = 0;
                for (var index in ResSortArray) {
                    ThisIndexInt = parseInt(index, 10) + 1;
                    $("#" + ResSortArray[index] + "Num").html(ThisIndexInt);
                    NewSort.push(ResSortArray[index].substr(8, 3));
                }
                NewSort = NewSort.join(",");
                if (NewSort != OldResSort) {
                    $("[name=resSort_changed]").val("1");
                    $("[name=resSort_array]").val(NewSort);
                } else {
                    if ($("[name=resSort_changed]").val() == "1") {
                        $("[name=resSort_changed]").val("");
                        $("[name=resSort_array]").val("");
                    }
                }
            }
        });

    $("[id^=\"fcp_\"]")
        .blur(function () {
            var ThisCP = $("#cp_" + $(this).attr("id"));
            HideTimeout = setTimeout(function () {
                FCPickers.hide("fast", function () {
                    ThisCP.hide(0);
                });
                HideTimeout = false;
            }, 50);
        })
        .focus(function () {
            if (HideTimeout !== false) {
                clearTimeout(HideTimeout);
            }
            var ThisID = $(this).attr("id");
            var ThisPicker = $("#cp_" + ThisID);
            if (!FCPickers.is(":visible")) {
                FCPickers.show("fast");
            }
            $(".fc_picker").hide(0);
            ThisPicker.show(0);
        })
        .keyup(function () {
            ColorPickerDivs[$(this).attr("id")].setColor($(this).val());
            if ($(this).val() == "") {
                $(this).css({background: ""});
            }
        });
    $("[id^=\"fcp_\"]").each(function () {
        var ThisID = $(this).attr("id");
        if (typeof ColorPickers[ThisID] == "undefined") {
            FCPickers.children("th").append("<div id=\"cp_" + ThisID + "\" class=\"fc_picker hide\"></div>");
            ColorPickers[ThisID] = $(this);
            ColorPickerDivs[ThisID] = $.farbtastic($("#cp_" + ThisID), function (color) {
                ColorPickers[ThisID].val(color).css({background: color});
            });
            ColorPickerDivs[ThisID].setColor($(this).val());
        }
    });

    setInterval(function () {
        var CurrentDate = new Date();
        var VD = new Date(CurrentDate.getTime() + VacationMiliseconds);
        $("#vacationBack").html(strpad(VD.getDate()) + "." + strpad(VD.getMonth() + 1) + "." + strpad(VD.getFullYear()) + " " + TemplateData["atHour"] + " " + strpad(VD.getHours()) + ":" + strpad(VD.getMinutes()) + ":" + strpad(VD.getSeconds()));
    }, 500);

    if ($("[name=\"skin_path\"]").val() != "") {
        $("[name=\"skin_path\"]").data("lastVal", $("[name=\"skin_path\"]").val());
        if (!$("[name=\"use_skin\"]").is(":checked")) {
            $("[name=\"use_skin\"]").prop("checked", true);
        }
    }
});

function strpad (val) {
    return (
        (!isNaN(val) && val.toString().length == 1) ?
            "0" + val :
            val
    );
}

function CompareVals (val1, val2, inputSelector) {
    if (val1 != "") {
        if (val1 == val2) {
            $(inputSelector).css({"border-color": "lime", "color": "lime"});
        } else {
            $(inputSelector).css({"border-color": "red", "color": "red"});
        }
    } else {
        $(inputSelector).css({"border-color": "#415680", "color": "#E6EBFB"});
    }
}

function CompareInputs (input1Selector, input2Selector) {
    var El1 = $(input1Selector);
    var El2 = $(input2Selector);
    if (El1.val() != "" && El2.val() != "") {
        if (El1.val() == El2.val()) {
            $(input1Selector).css({"border-color": "lime", "color": "lime"});
            $(input2Selector).css({"border-color": "lime", "color": "lime"});
        } else {
            $(input1Selector).css({"border-color": "red", "color": "red"});
            $(input2Selector).css({"border-color": "red", "color": "red"});
        }
    } else {
        $(El1).css({"border-color": "#415680", "color": "#E6EBFB"});
        $(El2).css({"border-color": "#415680", "color": "#E6EBFB"});
    }
}

/* eslint-disable */
function utf8_encode(argString){if(argString===null||typeof argString==="undefined"){return"";}
var string=(argString+'');var utftext="",start,end,stringl=0;start=end=0;stringl=string.length;for(var n=0;n<stringl;n++){var c1=string.charCodeAt(n);var enc=null;if(c1<128){end++;}else if(c1>127&&c1<2048){enc=String.fromCharCode((c1>>6)|192)+String.fromCharCode((c1&63)|128);}else{enc=String.fromCharCode((c1>>12)|224)+String.fromCharCode(((c1>>6)&63)|128)+String.fromCharCode((c1&63)|128);}
if(enc!==null){if(end>start){utftext+=string.slice(start,end);}
utftext+=enc;start=end=n+1;}}
if(end>start){utftext+=string.slice(start,stringl);}
return utftext;}
function md5(str){var xl;var rotateLeft=function(lValue,iShiftBits){return(lValue<<iShiftBits)|(lValue>>>(32-iShiftBits));};var addUnsigned=function(lX,lY){var lX4,lY4,lX8,lY8,lResult;lX8=(lX&0x80000000);lY8=(lY&0x80000000);lX4=(lX&0x40000000);lY4=(lY&0x40000000);lResult=(lX&0x3FFFFFFF)+(lY&0x3FFFFFFF);if(lX4&lY4){return(lResult^0x80000000^lX8^lY8);}
if(lX4|lY4){if(lResult&0x40000000){return(lResult^0xC0000000^lX8^lY8);}else{return(lResult^0x40000000^lX8^lY8);}}else{return(lResult^lX8^lY8);}};var _F=function(x,y,z){return(x&y)|((~x)&z);};var _G=function(x,y,z){return(x&z)|(y&(~z));};var _H=function(x,y,z){return(x^y^z);};var _I=function(x,y,z){return(y^(x|(~z)));};var _FF=function(a,b,c,d,x,s,ac){a=addUnsigned(a,addUnsigned(addUnsigned(_F(b,c,d),x),ac));return addUnsigned(rotateLeft(a,s),b);};var _GG=function(a,b,c,d,x,s,ac){a=addUnsigned(a,addUnsigned(addUnsigned(_G(b,c,d),x),ac));return addUnsigned(rotateLeft(a,s),b);};var _HH=function(a,b,c,d,x,s,ac){a=addUnsigned(a,addUnsigned(addUnsigned(_H(b,c,d),x),ac));return addUnsigned(rotateLeft(a,s),b);};var _II=function(a,b,c,d,x,s,ac){a=addUnsigned(a,addUnsigned(addUnsigned(_I(b,c,d),x),ac));return addUnsigned(rotateLeft(a,s),b);};var convertToWordArray=function(str){var lWordCount;var lMessageLength=str.length;var lNumberOfWords_temp1=lMessageLength+8;var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1%64))/64;var lNumberOfWords=(lNumberOfWords_temp2+1)*16;var lWordArray=new Array(lNumberOfWords-1);var lBytePosition=0;var lByteCount=0;while(lByteCount<lMessageLength){lWordCount=(lByteCount-(lByteCount%4))/4;lBytePosition=(lByteCount%4)*8;lWordArray[lWordCount]=(lWordArray[lWordCount]|(str.charCodeAt(lByteCount)<<lBytePosition));lByteCount++;}
lWordCount=(lByteCount-(lByteCount%4))/4;lBytePosition=(lByteCount%4)*8;lWordArray[lWordCount]=lWordArray[lWordCount]|(0x80<<lBytePosition);lWordArray[lNumberOfWords-2]=lMessageLength<<3;lWordArray[lNumberOfWords-1]=lMessageLength>>>29;return lWordArray;};var wordToHex=function(lValue){var wordToHexValue="",wordToHexValue_temp="",lByte,lCount;for(lCount=0;lCount<=3;lCount++){lByte=(lValue>>>(lCount*8))&255;wordToHexValue_temp="0"+lByte.toString(16);wordToHexValue=wordToHexValue+wordToHexValue_temp.substr(wordToHexValue_temp.length-2,2);}
return wordToHexValue;};var x=[],k,AA,BB,CC,DD,a,b,c,d,S11=7,S12=12,S13=17,S14=22,S21=5,S22=9,S23=14,S24=20,S31=4,S32=11,S33=16,S34=23,S41=6,S42=10,S43=15,S44=21;str=this.utf8_encode(str);x=convertToWordArray(str);a=0x67452301;b=0xEFCDAB89;c=0x98BADCFE;d=0x10325476;xl=x.length;for(k=0;k<xl;k+=16){AA=a;BB=b;CC=c;DD=d;a=_FF(a,b,c,d,x[k+0],S11,0xD76AA478);d=_FF(d,a,b,c,x[k+1],S12,0xE8C7B756);c=_FF(c,d,a,b,x[k+2],S13,0x242070DB);b=_FF(b,c,d,a,x[k+3],S14,0xC1BDCEEE);a=_FF(a,b,c,d,x[k+4],S11,0xF57C0FAF);d=_FF(d,a,b,c,x[k+5],S12,0x4787C62A);c=_FF(c,d,a,b,x[k+6],S13,0xA8304613);b=_FF(b,c,d,a,x[k+7],S14,0xFD469501);a=_FF(a,b,c,d,x[k+8],S11,0x698098D8);d=_FF(d,a,b,c,x[k+9],S12,0x8B44F7AF);c=_FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);b=_FF(b,c,d,a,x[k+11],S14,0x895CD7BE);a=_FF(a,b,c,d,x[k+12],S11,0x6B901122);d=_FF(d,a,b,c,x[k+13],S12,0xFD987193);c=_FF(c,d,a,b,x[k+14],S13,0xA679438E);b=_FF(b,c,d,a,x[k+15],S14,0x49B40821);a=_GG(a,b,c,d,x[k+1],S21,0xF61E2562);d=_GG(d,a,b,c,x[k+6],S22,0xC040B340);c=_GG(c,d,a,b,x[k+11],S23,0x265E5A51);b=_GG(b,c,d,a,x[k+0],S24,0xE9B6C7AA);a=_GG(a,b,c,d,x[k+5],S21,0xD62F105D);d=_GG(d,a,b,c,x[k+10],S22,0x2441453);c=_GG(c,d,a,b,x[k+15],S23,0xD8A1E681);b=_GG(b,c,d,a,x[k+4],S24,0xE7D3FBC8);a=_GG(a,b,c,d,x[k+9],S21,0x21E1CDE6);d=_GG(d,a,b,c,x[k+14],S22,0xC33707D6);c=_GG(c,d,a,b,x[k+3],S23,0xF4D50D87);b=_GG(b,c,d,a,x[k+8],S24,0x455A14ED);a=_GG(a,b,c,d,x[k+13],S21,0xA9E3E905);d=_GG(d,a,b,c,x[k+2],S22,0xFCEFA3F8);c=_GG(c,d,a,b,x[k+7],S23,0x676F02D9);b=_GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);a=_HH(a,b,c,d,x[k+5],S31,0xFFFA3942);d=_HH(d,a,b,c,x[k+8],S32,0x8771F681);c=_HH(c,d,a,b,x[k+11],S33,0x6D9D6122);b=_HH(b,c,d,a,x[k+14],S34,0xFDE5380C);a=_HH(a,b,c,d,x[k+1],S31,0xA4BEEA44);d=_HH(d,a,b,c,x[k+4],S32,0x4BDECFA9);c=_HH(c,d,a,b,x[k+7],S33,0xF6BB4B60);b=_HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);a=_HH(a,b,c,d,x[k+13],S31,0x289B7EC6);d=_HH(d,a,b,c,x[k+0],S32,0xEAA127FA);c=_HH(c,d,a,b,x[k+3],S33,0xD4EF3085);b=_HH(b,c,d,a,x[k+6],S34,0x4881D05);a=_HH(a,b,c,d,x[k+9],S31,0xD9D4D039);d=_HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);c=_HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);b=_HH(b,c,d,a,x[k+2],S34,0xC4AC5665);a=_II(a,b,c,d,x[k+0],S41,0xF4292244);d=_II(d,a,b,c,x[k+7],S42,0x432AFF97);c=_II(c,d,a,b,x[k+14],S43,0xAB9423A7);b=_II(b,c,d,a,x[k+5],S44,0xFC93A039);a=_II(a,b,c,d,x[k+12],S41,0x655B59C3);d=_II(d,a,b,c,x[k+3],S42,0x8F0CCC92);c=_II(c,d,a,b,x[k+10],S43,0xFFEFF47D);b=_II(b,c,d,a,x[k+1],S44,0x85845DD1);a=_II(a,b,c,d,x[k+8],S41,0x6FA87E4F);d=_II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);c=_II(c,d,a,b,x[k+6],S43,0xA3014314);b=_II(b,c,d,a,x[k+13],S44,0x4E0811A1);a=_II(a,b,c,d,x[k+4],S41,0xF7537E82);d=_II(d,a,b,c,x[k+11],S42,0xBD3AF235);c=_II(c,d,a,b,x[k+2],S43,0x2AD7D2BB);b=_II(b,c,d,a,x[k+9],S44,0xEB86D391);a=addUnsigned(a,AA);b=addUnsigned(b,BB);c=addUnsigned(c,CC);d=addUnsigned(d,DD);}
var temp=wordToHex(a)+wordToHex(b)+wordToHex(c)+wordToHex(d);return temp.toLowerCase();}
