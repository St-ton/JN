<div id="image_wrapper">
    <div class="row">
        <div class="col-xs-12">
            <button id="image_fullscreen_close" type="button" class="btn btn-primary pull-right"><span
                        aria-hidden="true">&times;</span></button>
        </div>
    </div>
    <div id="gallery_wrapper" class="clearfix">
        <div id="gallery">
            {block name="product-image"}
                {foreach $Artikel->Bilder as $image}
                    {strip}
                        <div>
                            {*<a href="{$image->cURLGross}" title="{$image->cAltAttribut|escape:"html"}">*}
                            <img src="{$imageBaseURL}gfx/trans.png" data-lazy="{$image->cURLNormal}"
                                 data-srcset="{$image->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                     {$image->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                     {$image->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w,
                                     {$image->cURLGross} {$Einstellungen.bilder.bilder_artikel_gross_breite}w"
                                 sizes="(max-width: 320px) 280px,
                                        (max-width: 480px) 440px,
                                        800px"
                                 alt="{$image->cAltAttribut|escape:"html"}"
                                 data-list='{$image->galleryJSON|replace:"'":"&apos;"}'
                                 data-largeimage="{$image->cURLGross}"/>
                            {*</a>*}
                        </div>
                    {/strip}
                {/foreach}
            {/block}
        </div>
    </div>
    <div id="gallery__preview_wrapper">
        <div id="gallery_preview">
            {block name="product-image"}
                {foreach $Artikel->Bilder as $image}
                    {strip}
                        <div>
                            <img src="{$imageBaseURL}gfx/trans.png" data-lazy="{$image->cURLMini}"
                                 alt="{$image->cAltAttribut|escape:"html"}"
                                 data-list='{$image->galleryJSON|replace:"'":"&apos;"}'/>
                        </div>
                    {/strip}
                {/foreach}
            {/block}
        </div>
    </div>
</div>



{*
<div id="gallery" class="hidden">
    {block name="product-image"}
    {foreach $Artikel->Bilder as $image}
        {strip}
            <a href="{$image->cURLGross}" title="{$image->cAltAttribut|escape:"html"}">
                <img src="{$image->cURLNormal}" alt="{$image->cAltAttribut|escape:"html"}" data-list='{$image->galleryJSON|replace:"'":"&apos;"}' />
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
*}
