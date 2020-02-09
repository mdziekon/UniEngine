<script>
var JSLang = {
    'Queue_CantCancel_Premium': '{Queue_CantCancel_Premium}',
    'Queue_ConfirmCancel': '{Queue_ConfirmCancel}'
};

$(document).ready(function () {
    var $cancelBtn = $('.cancelQueue');

    $cancelBtn.on('click', function () {
        var $thisElement = $(this);

        if ($thisElement.hasClass('premblock')) {
            alert(JSLang['Queue_CantCancel_Premium']);

            return false;
        }

        return confirm(JSLang['Queue_ConfirmCancel']);
    });
});
</script>

{Data_QueueElements}
