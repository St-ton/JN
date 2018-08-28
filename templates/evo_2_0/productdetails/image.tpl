{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<div class="gallery-with-action text-right" role="group">
    <div class="actions actions btn-group btn-group-xs btn-group-justified">
        {if $Einstellungen.artikeldetails.artikeldetails_vergleichsliste_anzeigen === 'Y'}
            <button name="Vergleichsliste" type="submit" class="compare badge" data-toggle="tooltip" data-placement="top" title="{lang key='addToCompare' section='productOverview'}">
                <img class="svg" src="{$imageBaseURL}gfx/compare.svg" alt="{lang key='addToCompare' section='productOverview'}" />
            </button>
        {/if}
        {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y'}
            <button name="Wunschliste" type="submit" class="wishlist badge" data-toggle="tooltip" data-placement="top" title="{lang key='addToWishlist' section='productDetails'}">
                <img class="svg" src="{$imageBaseURL}gfx/wishlist.svg" alt="{lang key='addToWishlist' section='productDetails'}" />
            </button>
        {/if}
    </div>
    <div id="gallery" class="hidden">
        {block name='product-image'}
            {foreach $Artikel->Bilder as $image}
                {strip}
                    <a href="{$image->cURLGross}" title="{$image->cAltAttribut|escape:'html'}">
                        <img src="{$image->cURLNormal}" alt="{$image->cAltAttribut|escape:'html'}" data-list='{$image->galleryJSON|replace:"'":"&apos;"}' />
                    </a>
                {/strip}
            {/foreach}
        {/block}
    </div>

    <div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="pswp__bg"></div>
        <div class="pswp__scroll-wrap">
            <div class="pswp__container">
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
            </div>
            <div class="pswp__ui pswp__ui--hidden">
                <div class="pswp__top-bar">
                    <div class="pswp__counter"></div>
                    <a class="pswp__button pswp__button--close" title="Close (Esc)"></a>
                    <a class="pswp__button pswp__button--share" title="Share"></a>
                    <a class="pswp__button pswp__button--fs" title="Toggle fullscreen"></a>
                    <a class="pswp__button pswp__button--zoom" title="Zoom in/out"></a>
                    <div class="pswp__preloader">
                        <div class="pswp__preloader__icn">
                            <div class="pswp__preloader__cut">
                                <div class="pswp__preloader__donut"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                    <div class="pswp__share-tooltip"></div>
                </div>
                <a class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
                </a>
                <a class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
                </a>
                <div class="pswp__caption">
                    <div class="pswp__caption__center"></div>
                </div>
            </div>
        </div>
    </div>

</div>
