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
                            {*sizes based on Evo template*}
                            <img src="{$image->cURLMini}" data-lazy="{$image->cURLMini}"
                                 data-srcset="{$image->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                     {$image->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                     {$image->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w,
                                     {$image->cURLGross} {$Einstellungen.bilder.bilder_artikel_gross_breite}w"
                                 sizes="(min-width: 1200px) 1080px,
                                        95vw"
                                 alt="{$image->cAltAttribut|escape:"html"}"
                                 data-list='{$image->galleryJSON|replace:"'":"&apos;"}'/>
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
                {if $Artikel->Bilder|@count > 1}
                {foreach $Artikel->Bilder as $image}
                    {strip}
                        <div>
                            <img src="{$imageBaseURL}gfx/trans.png" data-lazy="{$image->cURLMini}"
                                 alt="{$image->cAltAttribut|escape:"html"}"/>
                        </div>
                    {/strip}
                {/foreach}
                {/if}
            {/block}
        </div>
    </div>
</div>
