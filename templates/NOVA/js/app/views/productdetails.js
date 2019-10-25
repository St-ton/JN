
import { onMobile } from './../helpers.js'

const productImages		= '.product-detail .product-image'
const imageModal		= '#productImagesModal'

const $document			= $(document)
const $productImages	= $(productImages)

$document.on('click', productImages, onMobile(() => {
	$(imageModal).modal('show')
}))
