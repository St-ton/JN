
import 'bootstrap'
import 'bootstrap-select'

import 'shop/js/polyfills'

import 'shop/js/snippets/selectall'

import 'shop/js/components/accordions'
import 'shop/js/components/charts'
import 'shop/js/components/forms'
import 'shop/js/components/icons'
import 'shop/js/components/setup-assistant'

import 'shop/js/views/dashboard'
import 'shop/js/views/sidebar'
import 'shop/js/views/topbar'

/* init plugins */

$('[data-toggle="tooltip"]').tooltip()
$('.selectpicker').selectpicker({
	noneSelectedText: 'Nichts ausgewählt',
	noneResultsText: '{0} ergab keine Treffer',
	countSelectedText: '{0} ausgewählt',
	maxOptionsText: () => ['Limit erreicht (max. {n})', 'Gruppenlimit erreicht (max. {n})'],
	selectAllText: 'Alle aktivieren',
	deselectAllText: 'Alle deaktivieren',
	doneButtonText: 'Schließen'
})

/* events */

$(window).on('load', () => {
	$('#page-wrapper').removeClass('hidden disable-transitions')
	$('body').addClass('ready')
	$('body > .spinner').remove()
})
