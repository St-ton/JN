{assign var="productlist" value=$portlet->getFilteredProducts($instance)}
{assign var="style" value=$instance->getProperty('style')}

{if $style === 'gallery'}
    {assign var='grid' value='col-xs-6 col-lg-4'}
{else}
    {assign var='grid' value='col-xs-12'}
{/if}

{foreach $productlist as $product}
    {$product->cName}<br>
{/foreach}

{*
{if $productlist|@count > 0}
    <div {$instance->getAttributeString()}>
        <div class="row {if $style !== 'list'}row-eq-height row-eq-img-height{/if} {$style}" id="product-list">
            {foreach name=artikel from=$productlist item=Artikel}
                <div class="product-wrapper {$grid}">
                    <meta itemprop="position" content="{$smarty.foreach.artikel.iteration}">
                    {if $style === 'list'}
                        {include file='../../productlist/item_list.tpl' tplscope=$style}
                    {else}
                        {include file='../../productlist/item_box.tpl' tplscope=$style class='thumbnail'}
                    {/if}
                </div>
            {/foreach}
        </div>
    </div>
{/if}
*}