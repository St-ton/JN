
import { Sortable, Plugins } from '@shopify/draggable'

const Data = {
	ignore			: 'data-draggable-ignore',
	widgetAdd		: 'data-widget-add',
	widgetRemove	: 'data-widget-remove',
	widgetList		: 'data-widget-list'
}

const Classes = {
	dropzone		: 'sortable',
	draggable		: 'sortitem'
}

const $body			= $('body')

const dragDelay		= 200
const saveDelay		= 1000


let to = false


const sortable = new Sortable($(`.${Classes.dropzone}`).get(), {
	draggable: `.${Classes.draggable}`,
	mirror: {
		constrainDimensions: true,
		cursorOffsetX: 0,
		cursorOffsetY: 0
	},
	plugins: [Plugins.ResizeMirror],
	delay: dragDelay
})

/* events */

sortable.on('drag:start', (evt) => {
	let $target = $(evt.data.sensorEvent.data.target)

	$body.addClass('draggable--show-grid')

	clearTimeout(to)

	if($target.parents(`[${Data.ignore}]`).length > 0 || $target.is(`[${Data.ignore}]`)) {
		sortable.dragging = false
		evt.cancel()
	}
})

sortable.on('sortable:stop', (evt) => {
	to = setTimeout(() => {
		$body.removeClass('draggable--show-grid')
		// widget anordnung speichern
	}, saveDelay)
})

window.a = sortable

$(document).on('click', `[${Data.widgetAdd}]`, (e) => {
	e.preventDefault()
	// widget zum dashboard hinzufügen
})

$(document).on('click', `[${Data.widgetRemove}]`, (e) => {
	e.preventDefault()
	// widget vom dashboard entfernen und zur liste hinzufügen
})
