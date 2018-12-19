{assign var="productlist" value=$portlet->getFilteredProducts($instance)}
{assign var="style" value=$instance->getProperty('listStyle')}

{if $style === 'gallery'}
    {assign var='grid' value='col-xs-6 col-lg-4'}
{else}
    {assign var='grid' value='col-xs-12'}
{/if}

{if $style === 'slider'}
    {include file="./final.slider.tpl"}
{elseif $style === 'vertSlider'}
    {assign var="title" value=$instance->getProperty('sliderTitle')}
    <section class="panel{if !empty($title)} panel-default{/if}
                    panel-slider box box-slider
                    {if isset($class) && $class|strlen > 0} {$class}{/if}"
            {if isset($id) && $id|strlen > 0} id="{$id}"{/if}>
        <div class="panel-heading">
            {if !empty($title)}
                <h5 class="panel-title">
                    {$title}
                </h5>
            {/if}
        </div>
        <div{if $title|strlen > 0} class="panel-body"{/if}>
            <div class="evo-box-vertical text-center">
                {foreach name="sliderproducts" from=$productlist item='product'}
                    <div class="product-wrapper{if isset($style)} {$style}{/if}"
                            {if isset($Link) && $Link->getLinkType() == $smarty.const.LINKTYP_STARTSEITE}
                                itemprop="about"
                            {else}
                                itemprop="isRelatedTo"
                            {/if}
                         itemscope itemtype="http://schema.org/Product">
                        {include file='productlist/item_slider.tpl' Artikel=$product tplscope='box' class=''}
                    </div>
                {/foreach}
            </div>
        </div>
    </section>{* /panel *}
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