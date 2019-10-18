{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-image'}
    <div id="image_wrapper" class="gallery-with-action text-right mb-6" role="group">
        {row}
            {block name='productdetails-image-button'}
                {col cols=12}
                    <button id="image_fullscreen_close" type="button" class="btn btn-link float-right font-size-2.5x" aria-label="close">
                        <span aria-hidden="true"><i class="fa fa-times"></i></span>
                    </button>
                {/col}
            {/block}
            {block name='productdetails-image-main'}
                {col cols=12}
                    {if !($Artikel->nIstVater && $Artikel->kVaterArtikel == 0)}
                        {block name='productdetails-image-actions'}
                            <div class="actions btn-group btn-group-xs btn-group-justified" data-toggle="product-actions">
                                {if $Einstellungen.artikeldetails.artikeldetails_vergleichsliste_anzeigen === 'Y'}
                                    {block name='productdetails-image-include-comparelist-button'}
                                        {include file='snippets/comparelist_button.tpl'}
                                    {/block}
                                {/if}
                                {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y'}
                                    {block name='productdetails-image-include-wishlist-button'}
                                        {include file='snippets/wishlist_button.tpl'}
                                    {/block}
                                {/if}
                            </div>
                        {/block}
                    {/if}
                    {block name='productdetails-image-images'}
                        <div class="product-images productbox-images-detail carousel carousel-showcase" data-slick-group="productImages">
                            {foreach $Artikel->Bilder as $image}
                            <div>
                                <div class="productbox-image-wrapper">
                                    <div class="productbox-image-wrapper-inner">
                                        {strip}
                                            {assign var=pswp value='{&quot;src&quot;:&quot;'|cat:{$Artikel->Bilder[0]->cURLGross}|cat:'&quot;,&quot;w&quot;:1006,&quot;h&quot;:1006,&quot;i&quot;:5}'}
                                            {image alt=$image->cAltAttribut|escape:'html'
                                                class="product-image"
                                                fluid=true
                                                lazy=true
                                                webp=true
                                                src="{$Artikel->Bilder[0]->cURLNormal}"
                                                srcset="{$image->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                                    {$image->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                                    {$image->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w,
                                                    {$image->cURLGross} {$Einstellungen.bilder.bilder_artikel_gross_breite}w"
                                                sizes="auto"
                                                data=[
                                                    "list"=>"{$image->galleryJSON|escape:"html"}",
                                                    "pswp"=>"{$pswp}"
                                                ]
                                            }
                                        {/strip}
                                    </div>
                                </div>
                            </div>
                            {/foreach}
                        </div>
                    {/block}
                {/col}
            {/block}
            {block name='productdetails-image-preview'}
                {col cols=12}
                    {if $Artikel->Bilder|@count > 1}
                        <div class="product-thumbnails carousel carousel-thumbnails mb-5 mb-lg-0 d-none d-lg-flex mx-0" data-slick-group="productImages">
                            {block name='productdetails-image-preview-images'}
                                {foreach $Artikel->Bilder as $image}
                                    <div>
                                        <div class="productbox-image-wrapper">
                                            <div class="productbox-image-wrapper-inner p-2">
                                            {strip}
                                                {image alt=$image->cAltAttribut|escape:'html'
                                                    class="product-image"
                                                    fluid=true
                                                    lazy=true
                                                    webp=true
                                                    src="{$image->cURLKlein}"
                                                }
                                            {/strip}
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                            {/block}
                        </div>
                    {/if}
                {/col}
            {/block}
        {/row}
        {block name='productdetails-image-meta'}
            {foreach $Artikel->Bilder as $image}
                <meta itemprop="image" content="{$image->cURLNormal}">
            {/foreach}
        {/block}
    </div>
{/block}
