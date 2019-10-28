{**
* @copyright (c) JTL-Software-GmbH
* @license https://jtl-url.de/jtlshoplicense
*}
<div class="modal modal-fullview fade" id="productImagesModal" tabindex="-1" role="dialog" aria-labelledby="productImagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header p-0 border-0">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="fas fa-times"></span>
                </button>
            </div>

            <div class="modal-body">
                {foreach $images as $image}
                    <div class="productbox-image-wrapper">
                        <div class="productbox-image-wrapper-inner">
                            <img class="product-image img-fluid" src="{$image->cURLNormal}" width="1006" height="711" alt="">
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
</div>