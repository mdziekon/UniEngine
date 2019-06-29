/* Polish initialisation for the jQuery UI date picker plugin. */

jQuery(function ($) {
    var langCode = 'pl';

    $.datepicker.regional[langCode] = {
        // Strings
        closeText: 'Zamknij',
        prevText: '&#x3c;' + 'Poprzedni',
        nextText: 'Następny' + '&#x3e;',
        currentText: 'Dziś',
        monthNames: [
            'Styczeń',
            'Luty',
            'Marzec',
            'Kwiecień',
            'Maj',
            'Czerwiec',
            'Lipiec',
            'Sierpień',
            'Wrzesień',
            'Październik',
            'Listopad',
            'Grudzień'
        ],
        monthNamesShort: [
            'Sty',
            'Lut',
            'Mar',
            'Kwi',
            'Maj',
            'Cze',
            'Lip',
            'Sie',
            'Wrz',
            'Paź',
            'Lis',
            'Gru'
        ],
        dayNames: [
            'Niedziela',
            'Poniedziałek',
            'Wtorek',
            'Środa',
            'Czwartek',
            'Piątek',
            'Sobota'
        ],
        dayNamesShort: [
            'Nie',
            'Pn',
            'Wt',
            'Śr',
            'Czw',
            'Pt',
            'So'
        ],
        dayNamesMin: [
            'N',
            'Pn',
            'Wt',
            'Śr',
            'Cz',
            'Pt',
            'So'
        ],
        weekHeader: 'Tydź',

        // Settings
        dateFormat: 'yy-mm-dd',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };

    $.datepicker.setDefaults($.datepicker.regional[langCode]);

    if (!($.timepicker)) {
        return;
    }

    $.timepicker.regional[langCode] = {
        // Strings
        timeOnlyTitle: 'Wybierz Czas',
        timeText: 'Czas',
        hourText: 'Godzina',
        minuteText: 'Minuta',
        secondText: 'Sekunda',
        millisecText: 'Milisekunda',
        currentText: 'Teraz',
        closeText: 'Gotowe',

        // Settings
        timeFormat: 'hh:mm:ss',
        ampm: false
    };

    $.timepicker.setDefaults($.timepicker.regional[langCode]);
});
