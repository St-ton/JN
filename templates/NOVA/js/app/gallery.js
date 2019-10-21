/**
 * ------------------------------------------------------------------------
 * Constants
 * ------------------------------------------------------------------------
 */

const NAME					= 'gallery'
const VERSION				= '1.0.0'
const DATA_KEY				= `jtl.${NAME}`
const EVENT_KEY				= `.${DATA_KEY}`
const JQUERY_NO_CONFLICT	= $.fn[NAME]

const Default = {
	index : 0,
	history : false,
	showAnimationDuration : 500,
	hideAnimationDuration : 500
}

const Event = {
	CLICK			: `click${EVENT_KEY}`
}

/**
 * ------------------------------------------------------------------------
 * Class Definition
 * ------------------------------------------------------------------------
 */

class Gallery {
	constructor(element, config) {
		this._element = $(element)
		this._config = $.extend(true, {}, Default, config)

		this._items = []
		this._isSliderGallery = this._element.hasClass('slick-slider')


		this._setItems()
		this._bind()
	}

	/* Public */

	destroy() {
		this._element.find('a').unbind(EVENT_KEY)
		this._element = null
	}

	/* Private */

	_setItems() {
		let _this = this

		$.each(this._element.find('[data-pswp]:not(.slick-cloned)').filter(function() {
			return (($(this).parents('.slick-cloned').length == 0) && !$(this).hasClass('slick-cloned'))
		}), function() {
			_this._items.push($.parseJSON($(this).attr('data-pswp')))
			// $(this).attr('data-pswp', '')
		})
	}

	_bind() {
		let _this = this

		this._element.find('[data-pswp]').parent().on(Event.CLICK, function(e) {
			e.preventDefault()

			let imageLink = $(this),
				image = $(this).find('[data-pswp]')

			if(image.closest('.slick-slider').length > 0) {
				_this._config.index = parseInt(image.closest('.slick-slide').attr('data-slick-index'))
			} else {
				let images = image.closest('[data-gallery]').find('[data-pswp]')

				$.each(images, function(index, image2) {
					if(image.get(0) !== image2)
						return

					_this._config.index = index
				})
			}

			if($('.pswp').length == 0) {
				console.warn('PhotoSwipe Modal missing!')
				return false
			}

			try {
				let gallery = new PhotoSwipe($('.pswp')[0], PhotoSwipeUI_Default, _this._items, _this._config)
				gallery.init()
			} catch(e) {
				console.warn('PhotoSwipe is not initialized or is missing!')
			}
		})
	}

	/* Static */

	static _jQueryInterface(config) {
		config = config || {}

		let _arguments = arguments || null

		return this.each(function() {
			const $element	= $(this)
			let data		= $element.data(DATA_KEY)

			if(!data || typeof config === 'object') {
				data = new Gallery(this, config)
				$element.data(DATA_KEY, data)
			} else if(typeof data[config] === 'function') {
				data[config].apply(data, Array.prototype.slice.call(_arguments, 1))
			} else {
				$.error('Method ' +  config + ' does not exist.')
			}
		})
	}

}

/**
 * ------------------------------------------------------------------------
 * jQuery
 * ------------------------------------------------------------------------
 */

$.fn[NAME]             = Gallery._jQueryInterface
$.fn[NAME].Constructor = Gallery
$.fn[NAME].noConflict  = () => {
  $.fn[NAME] = JQUERY_NO_CONFLICT
  return Gallery._jQueryInterface
}

export default Gallery
