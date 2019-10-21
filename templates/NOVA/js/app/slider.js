const Defaults = {
	mobileFirst: true
}

/* productdetail gallery */
const initGalleries = ((i, element) => {

	$.each($('[data-slick-group].carousel-showcase:not(.slick-initialized)'), ((i, element) => {

		let group = $(element).attr('data-slick-group'),
			sliderShowcase = $(element),
			sliderThumbnails = $('[data-slick-group="'+group+'"].carousel-thumbnails')

		sliderShowcase.on('init', ((e, slick) => {
			let items = new Array

			$(slick).parent().addClass('init')

			$.each(sliderShowcase.find('[data-pswp]'), ((i, element) => {
				let imageProps = $.parseJSON($(element).attr('data-pswp'))

				items.push({
					src : imageProps.src,
					w : imageProps.w,
					h : imageProps.h,
					i : imageProps.i
				})
			}))

			sliderShowcase.find('[data-pswp]').on('click', ((i, element) => {
				let imageProps = $.parseJSON($(i.target).attr('data-pswp')),
					thumbnail = $(i.target)[0],
					options = {
						index : (imageProps.i) -1,
						history : false
					},
					gallery = new PhotoSwipe($('.pswp')[0], PhotoSwipeUI_Default, items, options)

				sliderShowcase.slick('pause')
				gallery.init()

				gallery.listen('beforeChange', ((i, element) => {
					sliderShowcase.slick('slickGoTo', gallery.getCurrentIndex())
				}))

				gallery.listen('afterChange', ((i, element) => {
					sliderShowcase.slick('slickGoTo', gallery.getCurrentIndex())
				}))

				gallery.listen('close', ((i, element) => {
					sliderShowcase.slick('play')
				}))
			}))
		}))

		// slick for thumbnails
		.slick($.extend(true, {}, Defaults, {
			rows: 0,
			arrows: false,
			fade: true,
			asNavFor: sliderThumbnails.get(0),
			dots: true,
			responsive: [{
				breakpoint: globals.breakpoints.lg,
				settings: {
					dots: false
				}
			}]
		}))

		sliderThumbnails.on('init', ((e, slick) => {
			if(slick.slideCount <= slick.options.slidesToShow)
				sliderThumbnails.addClass('no-transform')
		}))

		// slick for showcase
		.slick($.extend(true, {}, Defaults, {
			rows: 0,
			slidesToShow: 5,
			arrows: true,
			asNavFor: sliderShowcase.get(0),
			focusOnSelect: true
		}))

		window.sliderShowcase = sliderShowcase
		window.sliderThumbnails = sliderThumbnails

	}))
})

initGalleries()

// $('.productbox-images').slick($.extend(true, {}, Defaults, {
// 	rows: 0,
// 	dots: false,
// 	arrows: true
// }))

document.dispatchEvent(new CustomEvent('init-slider', { detail: { slick: $.slick, jquery: $ } }))
