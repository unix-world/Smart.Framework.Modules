/* Romanian initialisation for the jQuery time picker plugin. */
/* Written by Radu Ilies (iradu@unix-world.org) */
jQuery(function($){
	$.timepicker.regional['ro'] = {
				hourText: 'Ore',
				minuteText: 'Minute',
				amPmText: ['AM', 'PM'],
				closeButtonText: 'Închide',
				nowButtonText: 'Acum',
				deselectButtonText: 'Deselectează' }
	$.timepicker.setDefaults($.timepicker.regional['ro']);
});