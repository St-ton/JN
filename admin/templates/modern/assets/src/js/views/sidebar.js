
import Store from 'store'

const Classes = {
	iconView: 'icon-view'
}

const $sidebar = $('#sidebar')

const storeSidebarView = 'jtlshop-sidebar-view'

let sidebarView = Store.get(storeSidebarView)


if(sidebarView)
	$sidebar.addClass(Classes.iconView)

/* events */

$(document).on('click', '[data-toggle="sidebar-view"]', () => {
	Store.set(storeSidebarView, sidebarView ? false : true)
	$sidebar.toggleClass(Classes.iconView)
})
