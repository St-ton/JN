
export const debounce = (fn) => {
	let timeout
	return (...args) => {
		window.cancelAnimationFrame(timeout)
		timeout = requestAnimationFrame(() => fn(...args))
	}
}
