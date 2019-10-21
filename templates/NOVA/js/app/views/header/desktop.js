
/* imports */

import { backdrop, hasTouch, onDesktop, debounce } from './../../helpers.js'

/* vars */

const mainNavigation			= '#mainNavigationDesktop'
const navRightDropdowns			= 'header .nav-right .dropdown'
const navMenus					= 'header .navbar-nav > .dropdown'

const $document					= $(document)
const $window					= $(window)
const $backdrop					= backdrop().removeClass('fade')
const $mainNavigation			= $(mainNavigation)
const $navRightDropdowns		= $(navRightDropdowns)

const delayMenuFadeIn			= 400
const delayMenuFadeOut			= 200
const delayActiveMenuFadeIn		= 100

let menuInTo					= null
let menuOutTo					= null
let isDropdownActive			= false
let isMenuActive				= false
let $activeMenu					= null

/* functions */

$mainNavigation.navscrollbar()

const showMenu = (menu) => {
	$activeMenu = $(menu)
	$activeMenu.parent().addClass('show')
	$activeMenu.next().addClass('show')
	$activeMenu.attr('aria-expanded', true)

	$backdrop.insertBefore('header').addClass('zindex-dropdown show')
}

const hideMenu = () => {
	if($activeMenu === null)
		return

	$activeMenu.parent().removeClass('show')
	$activeMenu.next().removeClass('show')
	$activeMenu.attr('aria-expanded', false)
	$activeMenu = null
}

/* events */

$window.on('resize', debounce(() => {
	$(`${navRightDropdowns} .dropdown-menu`).dropdown('hide')
	hideMenu()
	$backdrop.removeClass('show').detach()
}))

$document.on('show.bs.dropdown', navRightDropdowns, (e) => {
	isDropdownActive = true
	hideMenu()
	$backdrop.insertBefore('header').addClass('zindex-dropdown show')
})

$document.on('hide.bs.dropdown', navRightDropdowns, (e) => {
	isDropdownActive = false

	if($activeMenu === null)
		$backdrop.removeClass('show').detach()
})

if(hasTouch()) {
	$document.on('click', `${navMenus} .dropdown-toggle`, onDesktop((e) => {
		e.preventDefault()

		if($activeMenu !== null && $activeMenu.get(0) === e.currentTarget) {
			hideMenu()
			$backdrop.removeClass('show').detach()
			return
		}

		if($activeMenu !== null)
			hideMenu()

		showMenu(e.currentTarget)
	}))

	$backdrop.on('click', onDesktop(() => {
		if($activeMenu !== null)
			hideMenu()

		if(!isDropdownActive)
			$backdrop.removeClass('show').detach()
	}))
}

$document.on('mouseenter', navMenus, onDesktop((e) => {
	if(hasTouch())
		return

	if(menuOutTo != undefined)
		clearTimeout(menuOutTo)

	let delay = delayMenuFadeIn
	let toggler = $(e.currentTarget).find('> .dropdown-toggle')

	if($activeMenu !== null && toggler.get(0) !== $activeMenu.get(0)) {
		hideMenu()
		delay = delayActiveMenuFadeIn
	}

	menuInTo = setTimeout(() => {
		showMenu($(e.currentTarget).find('> .dropdown-toggle'))
	}, delay)
})).on('mouseleave', navMenus, onDesktop((e) => {
	if(hasTouch())
		return

	if(menuInTo != undefined)
		clearTimeout(menuInTo)

	menuOutTo = setTimeout(() => {
		hideMenu()

		if(!isDropdownActive)
			$backdrop.removeClass('show').detach()
	}, delayMenuFadeOut)
}))

