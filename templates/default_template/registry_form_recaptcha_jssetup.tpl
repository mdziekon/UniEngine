<div
    class="captcha-indicator-el"
    style="display: none;"
></div>

<script type="text/javascript">
    var onRecaptchaVerifyCallback = function (response) {
        $("#captcha_response").val(response);
    };

    var onRecaptchaLoadCallback = function () {
        var $captchaContainer = document.querySelector(".captcha-container");

        grecaptcha.render(
            $captchaContainer,
            {
                "sitekey": "{Recaptcha_Sitekey}",
                "theme": "dark",

                "callback": function (response) {
                    $("#captcha_response").val(response);

                    // Prevent duplicated entry
                    $("[name='g-recaptcha-response']").val("");
                },
                "expired-callback": function () {
                    $("#captcha_response").val("");
                },
                "error-callback": function () {
                    $("#captcha_response").val("");
                }
            }
        );
    };
</script>
<script
    type="text/javascript"
    src="https://www.google.com/recaptcha/api.js?hl={Recaptcha_Lang}&onload=onRecaptchaLoadCallback&render=explicit"
    async
    defer
></script>
