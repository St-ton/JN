{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-image'}
    <div id="image_wrapper" class="gallery-with-action text-right mb-6" role="group">
        {row class="h-100"}
        {block name='productdetails-image-button'}
            {col cols=12 class="mb-4 product-detail-image-topbar"}
                {button id="image_fullscreen_close" variant="link" aria=["label"=>"close"]}
                    <span aria-hidden="true"><i class="fa fa-times"></i></span>
                {/button}
            {/col}
        {/block}
        {block name='productdetails-image-main'}
            {col cols=12}
            {if !($Artikel->nIstVater && $Artikel->kVaterArtikel == 0)}
                {block name='productdetails-image-actions'}
                    <div class="product-actions py-2" data-toggle="product-actions">
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
                <div id="gallery_wrapper" class="clearfix">
                    <div id="gallery" class="product-images slick-smooth-loading carousel">
                        {block name='productdetails-image-images'}
                            {foreach $Artikel->Bilder as $image}
                                {strip}
                                    <div>
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
                                            data=["list"=>"{$image->galleryJSON|escape:"html"}", "index"=>$image@index, "sizes"=>"auto"]
                                        }
                                    </div>
                                {/strip}
                            {/foreach}
                        {/block}
                    </div>
                </div>
            {/col}
        {/block}
        {block name='productdetails-image-preview'}
            {col cols=12 align-self='end' class='product-detail-image-preview-bar'}
            {if $Artikel->Bilder|@count > 1}
                <div id="gallery_preview_wrapper" class="mx-auto mt-4">
                    <div id="gallery_preview" class="product-thumbnails slick-smooth-loading carousel carousel-thumbnails mb-5 mb-lg-0 d-none d-lg-flex mx-0">
                        {block name='productdetails-image-preview-images'}
                            {foreach $Artikel->Bilder as $image}
                                {strip}
                                    <div>
                                        {image alt=$image->cAltAttribut|escape:'html'
                                            class="product-image"
                                            fluid=true
                                            lazy=true
                                            webp=true
                                            src="{$image->cURLKlein}"
                                        }
                                    </div>
                                {/strip}
                            {/foreach}
                        {/block}
                    </div>
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

        {block name='productdetails-image-include-product-images-modal'}
            {include file='productdetails/product_images_modal.tpl' images=$Artikel->Bilder}
        {/block}

        {block name='productdetails-image-variation-preview'}
            {if !$device->isMobile() && isset($Artikel->Variationen) && $Artikel->Variationen|@count > 0}
                {assign var=VariationsSource value='Variationen'}
                {if isset($ohneFreifeld) && $ohneFreifeld}
                    {assign var=VariationsSource value='VariationenOhneFreifeld'}
                {/if}
                {foreach name=Variationen from=$Artikel->$VariationsSource key=i item=Variation}
                    {foreach name=Variationswerte from=$Variation->Werte key=y item=Variationswert}
                        {if $Variationswert->getImage() !== null}
                            {image fluid=true webp=true lazy=true
                                class="variation-image-preview d-none fade vt{$Variationswert->kEigenschaftWert}"
                                src=$Variationswert->getImage(\JTL\Media\Image::SIZE_XS)
                                srcset="{$Variationswert->getImage(\JTL\Media\Image::SIZE_XS)} {$Einstellungen.bilder.bilder_variationen_mini_breite}w,
                                    {$Variationswert->getImage(\JTL\Media\Image::SIZE_SM)} {$Einstellungen.bilder.bilder_variationen_klein_breite}w,
                                    {$Variationswert->getImage(\JTL\Media\Image::SIZE_MD)} {$Einstellungen.bilder.bilder_variationen_breite}w,
                                    {$Variationswert->getImage(\JTL\Media\Image::SIZE_LG)} {$Einstellungen.bilder.bilder_variationen_gross_breite}w,"
                                sizes="50vw"
                                alt=$Variationswert->cName|escape:'quotes'
                            }
                        {/if}
                    {/foreach}
                {/foreach}
            {/if}
        {/block}
    </div>
{/block}
