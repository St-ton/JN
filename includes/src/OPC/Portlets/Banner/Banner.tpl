{$imgAttribs = $instance->getImageAttributes(null, null, null, 1, $portlet->getPlaceholderImgUrl())}

<div style="{$instance->getStyleString()}"
     class="banner {$instance->getAnimationClass()} {$instance->getStyleClasses()}"
     {$instance->getAnimationDataAttributeString()}>
    {image
        src=$imgAttribs.src
        srcset=$imgAttribs.srcset
        sizes=$imgAttribs.srcsizes
        alt=$imgAttribs.alt
        title=$imgAttribs.title
        fluid=true
    }
    {if !$isPreview}
        {foreach $instance->getProperty('zones') as $zone}
            {$product = null}
            {$title   = ''}
            {$url     = ''}
            {$desc    = ''}
            {if $zone.productId > 0}
                {$product = $portlet->getProduct($zone.productId)}
                {$title   = $product->cName}
                {$url     = $product->cURLFull}
                {$desc    = $product->cKurzBeschreibung}
            {/if}
            {if !empty($zone.title)}
                {$title = $zone.title}
            {/if}
            {if !empty($zone.url)}
                {$url = $zone.url}
            {/if}
            {if !empty($zone.desc)}
                {$desc = $zone.desc}
            {/if}
            <a class="area {$zone.class}" href="{$url}" {if $zone.target}target="_blank"{/if} title="{$title|strip_tags|escape:'html'|escape:'quotes'}"
               style="left: {$zone.left}%; top: {$zone.top}%; width: {$zone.width}%; height: {$zone.height}%;">
                <div class="area-desc">
                    {if !empty($product) > 0}
                        {image
                            src=$product->cVorschaubildURL
                            alt=$product->cName|strip_tags|escape:'quotes'|truncate:60
                            style='display: block; margin-left: auto; margin-right: auto'
                            fluid=true}
                        {include file='productdetails/price.tpl' Artikel=$product tplscope="box"}
                    {/if}
                    {if $desc|@strlen > 0}
                        <p>{$desc}</p>
                    {/if}
                </div>
            </a>
        {/foreach}
    {/if}
</div>