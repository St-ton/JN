{$style = $instance->getProperty('listStyle')}

{if $isPreview}
    <div class="opc-ProductStream">
        {image alt='ProductStream' src=$portlet->getBaseUrl()|cat:'preview.'|cat:$style|cat:'.png'}
    </div>
{else}
    {$productlist = $portlet->getFilteredProducts($instance)}
    
    {if $style === 'list' || $style === 'gallery'}
        {if $style === 'list'}
            {$grid = '12'}
        {else}
            {$grid   = '6'}
            {$gridmd = '4'}
            {$gridxl = '3'}
        {/if}
        {row class=$style|cat:' product-list opc-ProductStream opc-ProductStream-'|cat:$style|cat:' '|cat:$instance->getStyleClasses()
             itemprop="mainEntity" itemscope=true itemtype="http://schema.org/ItemList"}
            {foreach $productlist as $Artikel}
                {col cols={$grid} md="{if isset($gridmd)}{$gridmd}{/if}" xl="{if isset($gridxl)}{$gridxl}{/if}"
                     class="product-wrapper {if !($style === 'list' && $Artikel@last)}mb-4{/if}"
                     itemprop="itemListElement" itemscope=true itemtype="http://schema.org/Product"}
                    {if $style === 'list'}
                        {include file='productlist/item_list.tpl' tplscope=$style}
                    {elseif $style === 'gallery'}
                        {include file='productlist/item_box.tpl' tplscope=$style}
                    {/if}
                {/col}
            {/foreach}
        {/row}
    {elseif $style === 'simpleSlider'}
        <div id="{$instance->getUid()}"
             class="carousel carousel-arrows-inside slick-lazy
                    opc-ProductStream opc-ProductStream-slider slick-type-product"
             data-slick-type="product-slider">
            {foreach $productlist as $Artikel}
                <div class="product-wrapper">
                    <a href="{$Artikel->cURLFull}">
                        <div class="square square-image">
                            <div class="inner">
                                <img src="{$Artikel->Bilder[0]->cURLNormal}" alt="{$Artikel->cName}" title="{$Artikel->cName}">
                            </div>
                        </div>
                    </a>
                </div>
            {/foreach}
        </div>
    {elseif $style === 'slider'}
        {include file='snippets/product_slider.tpl' productlist=$productlist}
    {/if}
{/if}