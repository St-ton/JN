
const modal					= '#modal-setup-assistant'
const dataPrefix			= 'data-setup'

const Data = {
	current					: `${dataPrefix}-current`,
	step					: `${dataPrefix}-step`,
	slides					: `${dataPrefix}-slide`,
	prev					: `${dataPrefix}-prev`,
	next					: `${dataPrefix}-next`,
	submit					: `${dataPrefix}-submit`,
	legalToggler			: `${dataPrefix}-legal-toggle`,
	legalPlugins			: `${dataPrefix}-legal-plugins`,
	summaryPlaceholder		: `${dataPrefix}-summary-placeholder`,
	summaryId				: `${dataPrefix}-summary-id`,
	summaryText				: `${dataPrefix}-summary-text`
}

const $modal				= $(modal)
const $form					= $(`${modal} form`)
const $step					= $(`${modal} [${Data.step}]`)
const $slides				= $(`${modal} [${Data.slides}]`)
const $prev					= $(`${modal} [${Data.prev}]`)
const $next					= $(`${modal} [${Data.next}]`)
const $submit				= $(`${modal} [${Data.submit}]`)
const $legalplugins			= $(`${modal} [${Data.legalPlugins}]`)
const $summaryPlaceholder	= $(`${modal} [${Data.summaryPlaceholder}]`)
const $summaryId			= $(`${modal} [${Data.summaryId}]`)
const $summaryText			= $(`${modal} [${Data.summaryText}]`)

const last					= $slides.length - 1


let current					= 0
let subsequent				= false

let $currentSlide			= $(`${modal} [${Data.slides}='${current}']`)


const showSlide = slide => {
	if(slide === current && subsequent)
		return

	current = slide

	$modal.attr(Data.current, current)

	$currentSlide = $(`${modal} [${Data.slides}='${slide}']`)

	$step.removeClass('active')
	$step.filter(function() {
		return $(this).is(`[${Data.step}="${slide}"]`)
	}).addClass('active')

	$slides.removeClass('active')
	$currentSlide.addClass('active')
}

const updateSummary = (slide = current) => {
	let summaries = {}
	let $summaries = $currentSlide.find(`[${Data.summaryId}]`)

	$.each($summaries, (index, summary) => {
		let $summary = $(summary)
		let isCheckbox = $summary.is(':checkbox')
		let isRadio = $summary.is(':radio')
		let isSelect = $summary.is('select')
		let id = $summary.attr(`${Data.summaryId}`)

		if(summaries[id] === undefined)
			summaries[id] = []

		if(isCheckbox || isRadio) {
			if(!$summary.is(':checked'))
				return

			summaries[id].push($summary.attr(`${Data.summaryText}`))
		} else if(isSelect) {
			let $checked = $summary.find('option:checked')

			if($checked.attr(`${summary}`) === undefined)
				return

			summaries[id].push($checked.attr(`${Data.summaryText}`))
		} else {
			if($summary.val() === '')
				return

			summaries[id].push($summary.val())
		}
	})

	$.each(summaries, (i, a) => {
		let $placeholder = $(`[${Data.summaryPlaceholder}="${i}"]`)
		$placeholder.html(a.join(', '))
	})
}

/* events */

$(document).on('click', `${modal} [${Data.step}]:not(.active):not(.active ~ [${Data.step}])`, function(e) {
	e.preventDefault()

	let slide = parseInt($(this).attr(Data.step))
	showSlide(slide)
})

$(document).on('click', `${modal} [${Data.prev}]`, () => {
	showSlide((current > 0) ? current - 1 : current)
})

$(document).on('click', `${modal} [${Data.next}]`, () => {
	updateSummary()
	showSlide((current < last) ? current + 1 : last)
})

$(document).on('change', `${modal} [${Data.legalToggler}]`, function() {
	let usePlugin = $(this).val() > 0

	if(usePlugin) {
		$legalplugins.removeClass('disabled')
	} else {
		$legalplugins.addClass('disabled')
	}
})

$form.on('submit', (e) => {
	e.preventDefault()

	$submit.addClass('disabled').attr('disabled', true)
	$prev.addClass('disabled').attr('disabled', true)

	let callback = new Promise((resolve, reject) => {
		// simulate success

		let checkmark = `<span class="fal fa-check text-success fa-fw"></span>`

		setTimeout(() => {
			$currentSlide.find(`[${Data.step}="1"]`).html(checkmark)
		}, 500)

		setTimeout(() => {
			$currentSlide.find(`[${Data.step}="2"]`).html(checkmark)
		}, 1000)

		setTimeout(() => {
			$currentSlide.find(`[${Data.step}="3"]`).html(checkmark)
		}, 1500)

		setTimeout(() => {
			$currentSlide.find(`[${Data.step}="4"]`).html(checkmark)
		}, 2000)

		setTimeout(() => {
			resolve()
		}, 3000)
	});

	callback.then(() => {
		$submit.removeClass('disabled').attr('disabled', false)
		$prev.removeClass('disabled').attr('disabled', false)
		$modal.addClass('installed')
	}).catch((msg) => {
		$submit.removeClass('disabled').attr('disabled', false)
		$prev.removeClass('disabled').attr('disabled', false)
	})
})

$modal.on('show.bs.modal', () => {
	showSlide(current)
	subsequent = true
})
