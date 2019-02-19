{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<div id="image_wrapper" class="gallery-with-action text-right mb-6" role="group">
        {row}
        {col cols=12}
            <button id="image_fullscreen_close" type="button" class="btn btn-primary float-right md-3" aria-label="close"><span
                        aria-hidden="true"><i class="fa fa-times"></i></span></button>
        {/col}
        {col cols=12}
            {if !($Artikel->nIstVater && $Artikel->kVaterArtikel == 0)}
                <div class="actions btn-group btn-group-xs btn-group-justified">
                    {if $Einstellungen.artikeldetails.artikeldetails_vergleichsliste_anzeigen === 'Y'}
                        {button name="Vergleichsliste" type="submit" class="compare badge mr-3 mt-3"
                        title="{lang key='addToCompare' section='productOverview'}"
                        data=["toggle"=>"tooltip", "placement"=>"top"]
                        }
                            {image class="svg" src="{$currentTemplateDir}themes/base/images/compare.svg" alt="{lang key='addToCompare' section='productOverview'}"}
                        {/button}
                    {/if}
                    {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y'}
                        {button name="Wunschliste" type="submit" class="wishlist badge mr-3 mt-3"
                        title="{lang key='addToWishlist' section='productDetails'}"
                        data=["toggle"=>"tooltip", "placement"=>"top"]
                        }
                            {image class="svg" src="{$currentTemplateDir}themes/base/images/wishlist.svg" alt="{lang key='addToWishlist' section='productDetails'}"}
                        {/button}
                    {/if}
                </div>
            {/if}
            <div id="gallery_wrapper" class="clearfix">
                <div id="gallery" class="mb-3">
                    {block name="product-image"}
                        {foreach $Artikel->Bilder as $image}
                            {strip}
                                <div>
                                    {*sizes based on template*}
                                    {image alt=$image->cAltAttribut|escape:'html'
                                        data=["lazy"=>$image->cURLMini,
                                            "srcset"=>"{$image->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                                {$image->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                                {$image->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w,
                                                {$image->cURLGross} {$Einstellungen.bilder.bilder_artikel_gross_breite}w",
                                            "list"=>"{$image->galleryJSON|escape:"html"}"
                                        ]
                                        sizes="(min-width: 1200px) 1080px,90vw"
                                        src=$image->cURLMini
                                     }
                                </div>
                            {/strip}
                        {/foreach}
                    {/block}
                </div>
            </div>
        {/col}
        {col cols=12 align-self='end'}
            {if $Artikel->Bilder|@count > 1}
                <div id="gallery_preview_wrapper" class="mx-auto">
                    <div id="gallery_preview">
                        {block name="product-image"}
                            {foreach $Artikel->Bilder as $image}
                                {strip}
                                    <div>
                                        {image src="{$imageBaseURL}gfx/trans.png" data=["lazy"=>"{$image->cURLMini}"]
                                             alt="{$image->cAltAttribut|escape:"html"}"}
                                    </div>
                                {/strip}
                            {/foreach}
                        {/block}
                    </div>
                </div>
            {/if}
        {/col}
    {/row}
    {foreach $Artikel->Bilder as $image}
        <meta itemprop="image" content="{$image->cURLNormal}">
    {/foreach}
</div>
