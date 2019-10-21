
/* imports */

import {
	onMobile, lockScreen, unlockScreen, backdrop, debounce
} from './../../helpers.js'

/* vars */

const mainNavigation			= '#mainNavigationMobile'
const menuLinks					= '#mainNavigationMobile [data-show-menu-id]'
const menuBack					= '#mainNavigationMobile [data-menu-back]'

const $document					= $(document)
const $window					= $(window)
const $backdrop					= backdrop().addClass('zindex-dropdown')
const $menuLinks				= $(menuLinks)
const $menuBack					= $(menuBack)
const $mainNavigation			= $(mainNavigation)

/* functions */

const showParent = (menuID) => {
	let $currentMenu	= $mainNavigation.find(`[data-menu-id="${menuID}"]`)
	let parentMenuID	= $currentMenu.attr('data-parent-menu-id')
	let $nextMenu		= $mainNavigation.find(`[data-menu-id="${parentMenuID}"]`)

	updateMenuTitle(parentMenuID)

	if($nextMenu.length === 0)
		return

	$currentMenu.removeClass('nav-current').addClass('nav-child')
	$nextMenu.removeClass('nav-parent').addClass('nav-current')
}

const showChild = (menuID) => {
	let $currentMenu	= $mainNavigation.find('[data-menu-id].nav-current')
	let $nextMenu		= $mainNavigation.find(`[data-menu-id="${menuID}"]`)

	if($nextMenu.length === 0)
		return

	updateMenuTitle(menuID)

	$currentMenu.removeClass('nav-current').addClass('nav-parent')
	$nextMenu.removeClass('nav-child').addClass('nav-current')
}

const updateMenuTitle = (menuID) => {
	if(menuID === undefined || menuID == 0) {
		$('span.nav-offcanvas-title').removeClass('d-none')
		$('a.nav-offcanvas-title').addClass('d-none')
	} else {
		$('span.nav-offcanvas-title').addClass('d-none')
		$('a.nav-offcanvas-title').removeClass('d-none')
	}
}

const updateVH = () => {
	let vh = window.innerHeight * .01
	document.documentElement.style.setProperty('--vh', `${vh}px`)
}

updateVH()

/* events */

$window.on('resize', debounce(() => {
	$mainNavigation.collapse('hide')
	updateVH()
}))

$backdrop.on('click', onMobile(() => {
	$mainNavigation.collapse('hide')
}))

$document.on('click', menuLinks, onMobile((e) => {
	e.preventDefault()
	showChild($(e.currentTarget).attr('data-show-menu-id'))
}))

$document.on('click', menuBack, onMobile((e) => {
	e.preventDefault()
	showParent($mainNavigation.find('[data-menu-id].nav-current').attr('data-menu-id'))
}))

$document.on('show.bs.collapse', mainNavigation, () => {
	lockScreen()
	$backdrop.insertBefore($mainNavigation)
})

$document.on('shown.bs.collapse', mainNavigation, () => {
	$backdrop.addClass('show')
})

$document.on('hide.bs.collapse', mainNavigation, () => {
	$backdrop.removeClass('show')
})

$document.on('hidden.bs.collapse', mainNavigation, () => {
	unlockScreen()
	$backdrop.detach()
})
