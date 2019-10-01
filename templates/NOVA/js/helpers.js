const breakpoints = {
    xl: 1300,
    lg: 992,
    md: 768,
    sm: 576
}

window.globals = {
    breakpoints: breakpoints
}

let isMobile = () => window.innerWidth < globals.breakpoints.lg
let isDesktop = () => !isMobile()
let hasTouch = () => 'ontouchstart' in window

const debounce = (fn, wait = 100) => {
	let timeout
	return (...args) => {
		clearTimeout(timeout)
		timeout = setTimeout(() => fn(...args), wait)
	}
}

const throttle = (fn, wait = 100) => {
	let timeout
	return (...args) => {
		if(timeout)
			return

		fn(...args)
		timeout = true
		setTimeout(() => timeout = false, wait)
	}
}

const uniqid = () => {
	return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1) + Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1)
}

const onMobile = (fn) => {
	return (...args) => {
		if(isMobile()) fn(...args)
	}
}

const onDesktop = (fn) => {
	return (...args) => {
		if(!isMobile()) fn(...args)
	}
}

window.addEventListener('resize', debounce(() => {
	isMobile = window.innerWidth < globals.breakpoints.lg
}))
