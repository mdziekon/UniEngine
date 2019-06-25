/* English (en-GB) translations for the jQuery UI date picker plugin. */

jQuery(function ($) {
    var langCode = 'en-GB';

    $.datepicker.regional[langCode] = {
        // Strings
        closeText: 'Close',
        prevText: '&#x3c;' + 'Previous',
        nextText: 'Next' + '&#x3e;',
        currentText: 'Today',
        monthNames: [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ],
        monthNamesShort: [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
        ],
        dayNames: [
            'Sunday',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday'
        ],
        dayNamesShort: [
            'Su',
            'Mo',
            'Tu',
            'We',
            'Thu',
            'Fri',
            'Sat'
        ],
        dayNamesMin: [
            'Su',
            'Mo',
            'Tu',
            'We',
            'Th',
            'Fr',
            'Sa'
        ],
        weekHeader: 'Wk',

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
        timeOnlyTitle: 'Select time',
        timeText: 'Time',
        hourText: 'Hour',
        minuteText: 'Minute',
        secondText: 'Second',
        millisecText: 'Milisecond',
        currentText: 'Now',
        closeText: 'Done',

        // Settings
        imeFormat: 'hh:mm:ss',
        ampm: false
    };

    $.timepicker.setDefaults($.timepicker.regional[langCode]);
});
