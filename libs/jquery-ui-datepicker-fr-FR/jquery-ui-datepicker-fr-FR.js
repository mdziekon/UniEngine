/* French (fr-FR) translations for the jQuery UI date picker plugin. */

jQuery(function ($) {
    var langCode = 'fr-FR';

    $.datepicker.regional[langCode] = {
        // Strings
        closeText: 'Fermer',
        prevText: '&#x3c;' + 'Précédent',
        nextText: 'Suivant' + '&#x3e;',
        currentText: 'Aujourd\'hui',
        monthNames: [
            'Janvier',
            'Février',
            'Mars',
            'Avril',
            'Mai',
            'Juin',
            'Juillet',
            'Août',
            'Septembre',
            'Octobre',
            'Novembre',
            'Décembre'
        ],
        monthNamesShort: [
            'Jan',
            'Fév',
            'Mars',
            'Avr',
            'Mai',
            'Juin',
            'Juil',
            'Août',
            'Sep',
            'Oct',
            'Nov',
            'Déc'
        ],
        dayNames: [
            'Dimanche',
            'Lundi',
            'Mardi',
            'Mercredi',
            'Jeudi',
            'Vendredi',
            'Samedi'
        ],
        dayNamesShort: [
            'Dim',
            'Lun',
            'Mar',
            'Mer',
            'Jeu',
            'Ven',
            'Sam'
        ],
        dayNamesMin: [
            'Di',
            'Lu',
            'Ma',
            'Me',
            'Je',
            'Ve',
            'Sa'
        ],
        weekHeader: 'Sem',

        // Settings
        dateFormat: 'dd-mm-yy',
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
        timeOnlyTitle: 'Définir l\'heure',
        timeText: 'Heure',
        hourText: 'Heure',
        minuteText: 'Minute',
        secondText: 'Seconde',
        millisecText: 'Milliseconde',
        currentText: 'Maintenant',
        closeText: 'Fermer',

        // Settings
        imeFormat: 'hh:mm:ss',
        ampm: false
    };

    $.timepicker.setDefaults($.timepicker.regional[langCode]);
});
