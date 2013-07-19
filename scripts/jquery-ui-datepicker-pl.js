/* Polish initialisation for the jQuery UI date picker plugin. */
/* Written by Jacek Wysocki (jacek.wysocki@gmail.com). */
jQuery(function($){
	$.datepicker.regional['pl'] = 
            {
		closeText: 'Zamknij',
		prevText: '&#x3c;Poprzedni',
		nextText: 'Następny&#x3e;',
		currentText: 'Dziś',
		monthNames: ['Styczeń','Luty','Marzec','Kwiecień','Maj','Czerwiec',
		'Lipiec','Sierpień','Wrzesień','Październik','Listopad','Grudzień'],
		monthNamesShort: ['Sty','Lut','Mar','Kwi','Maj','Cze',
		'Lip','Sie','Wrz','Paź','Lis','Gru'],
		dayNames: ['Niedziela','Poniedziałek','Wtorek','Środa','Czwartek','Piątek','Sobota'],
		dayNamesShort: ['Nie','Pn','Wt','Śr','Czw','Pt','So'],
		dayNamesMin: ['N','Pn','Wt','Śr','Cz','Pt','So'],
		weekHeader: 'Tydź',
		dateFormat: 'yy-mm-dd',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''
            };
	$.datepicker.setDefaults($.datepicker.regional['pl']);
    
	if(typeof $.timepicker != 'undefined')
	{
        $.timepicker.regional['pl'] = 
            {
                timeOnlyTitle: 'Wybierz Czas',
                timeText: 'Czas',
                hourText: 'Godzina',
                minuteText: 'Minuta',
                secondText: 'Sekunda',
                millisecText: 'Milisekunda',
                currentText: 'Teraz',
                closeText: 'Gotowe',
                ampm: false
            };
        $.timepicker.setDefaults($.timepicker.regional['pl']);
	}
});
