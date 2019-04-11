{$imgAttribs = $instance->getImageAttributes(null, null, null, 1, $portlet->getPlaceholderImgUrl())}

{if $isPreview}
    <div style="text-align: center;" {$instance->getAttributeString()} {$instance->getDataAttributeString()} >
        {image
            src=$imgAttribs.src
            srcset=$imgAttribs.srcset
            sizes=$imgAttribs.srcsizes
            alt=$imgAttribs.alt
            style='filter: grayscale(50%) opacity(60%)'
            title=$imgAttribs.title
            fluid=true
        }
        <p style="color: #5cbcf6; font-size: 40px; font-weight: bold; margin-top: -56px;">Banner</p>
    </div>
{else}
    <div class="banner" style="{$instance->getStyleString()}">
        {image
            src=$imgAttribs.src
            srcset=$imgAttribs.srcset
            sizes=$imgAttribs.srcsizes
            alt=$imgAttribs.alt
            title=$imgAttribs.title
            fluid=true
        }
        {foreach $instance->getProperty('zones') as $zone}
            {$product = null}
            {$title   = ''}
            {$url     = ''}
            {$desc    = ''}
            {if $zone.productId > 0}
                {$product = $portlet->getProduct($zone.productId)}
                {$title   = $product->cName}
                {$url     = $product->cURL}
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
            <a class="area {$zone.class}" href="{$url}" title="{$title|strip_tags|escape:'html'|escape:'quotes'}"
               style="left: {$zone.left}%; top: {$zone.top}%; width: {$zone.width}%; height: {$zone.height}%;">
                <div class="area-desc">
                    {if !empty($product) > 0}
                        {image
                            src=$product->cVorschaubild
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
    </div>
{/if}