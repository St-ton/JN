
// import { Sortable, Plugins } from '@shopify/draggable'

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

const dragDelay		= 0
const saveDelay		= 0


let to = false


const sortable = new Draggable.Sortable($(`.${Classes.dropzone}`).get(), {
	draggable: `.${Classes.draggable}`,
	mirror: {
		constrainDimensions: true,
		cursorOffsetX: 0,
		cursorOffsetY: 0
	},
	plugins: [Draggable.Plugins.ResizeMirror],
	delay: dragDelay
})

/* events */

sortable.on('drag:start', (evt) => {
	let $target = $(evt.data.sensorEvent.data.target)

	clearTimeout(to)

	if($target.parents(`[${Data.ignore}]`).length > 0 || $target.is(`[${Data.ignore}]`)) {
		sortable.dragging = false
		evt.cancel()
	} else {
		$body.addClass('draggable--show-grid')
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
