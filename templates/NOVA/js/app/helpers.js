
export let isMobile = () => window.innerWidth < globals.breakpoints.lg
export let isDesktop = () => !isMobile()
export let hasTouch = () => 'ontouchstart' in window

export const debounce = (fn, wait = 100) => {
	let timeout
	return (...args) => {
		clearTimeout(timeout)
		timeout = setTimeout(() => fn(...args), wait)
	}
}

export const throttle = (fn, wait = 100) => {
	let timeout
	return (...args) => {
		if(timeout)
			return

		fn(...args)
		timeout = true
		setTimeout(() => timeout = false, wait)
	}
}

export const uniqid = () => {
	return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1) + Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1)
}

export const onMobile = (fn) => {
	return (...args) => {
		if(isMobile()) fn(...args)
	}
}

export const onDesktop = (fn) => {
	return (...args) => {
		if(!isMobile()) fn(...args)
	}
}
