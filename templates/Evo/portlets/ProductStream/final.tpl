{assign var="productlist" value=$portlet->getFilteredProducts($instance)}
{assign var="style" value=$instance->getProperty('listStyle')}

{if $style === 'gallery'}
    {assign var='grid' value='col-xs-6 col-lg-4'}
{else}
    {assign var='grid' value='col-xs-12'}
{/if}

{if $style === 'slider'}
    {include file="./final.slider.tpl"}
{else}
    <div id="result-wrapper">
        <div {$instance->getAttributeString()}>
            <div class="row {if $style !== 'list'}row-eq-height row-eq-img-height{/if} {$style}" id="product-list">
                {foreach name=artikel from=$portlet->getFilteredProducts($instance) item=Artikel}
                    <div class="product-wrapper {$grid}">
                        {if $style === 'list'}
                            {include file='productlist/item_list.tpl' tplscope=$style}
                        {else}
                            {include file='productlist/item_box.tpl' tplscope=$style class='thumbnail'}
                        {/if}
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
{/if}