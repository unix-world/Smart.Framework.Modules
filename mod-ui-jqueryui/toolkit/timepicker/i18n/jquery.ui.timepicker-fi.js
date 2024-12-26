/* Finnish initialisation for the jQuery time picker plugin. */
/* Written by Radu Ilies (iradu@unix-world.org) */
jQuery(function($){
	$.timepicker.regional['fi'] = {
				hourText: 'Tunnit',
				minuteText: 'Minuutit',
				amPmText: ['AM', 'PM'],
				closeButtonText: 'Sulje',
				nowButtonText: 'Nyt',
				deselectButtonText: 'Poista' }
	$.timepicker.setDefaults($.timepicker.regional['fi']);
});