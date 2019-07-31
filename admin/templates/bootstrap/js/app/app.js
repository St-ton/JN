
// import 'bootstrap'
// import 'bootstrap-select'

import './polyfills.js'

import './plugins/tabdrop.js'

import './snippets/selectall.js'
import './snippets/form-counter.js'

import './components/accordions.js'
import './components/charts.js'
import './components/forms.js'
//import 'shop/js/components/icons'
import './components/setup-assistant.js'

import './views/dashboard.js'
import './views/sidebar.js'
import './views/topbar.js'

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
$('.nav-tabs, .nav-pills').tabdrop()

/* events */

$(window).on('load', () => {
	$('#page-wrapper').removeClass('hidden disable-transitions')
	$('html').addClass('ready')
	$('body > .spinner').remove()

	document.dispatchEvent(new CustomEvent('ready', {
		detail: {
			jquery : $
		}
	}))
})
