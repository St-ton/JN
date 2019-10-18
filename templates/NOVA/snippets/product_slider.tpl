{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-product-slider'}
    {strip}
    {if $productlist|@count > 0}
        {if !isset($tplscope)}
            {assign var=tplscope value='slider'}
        {/if}

        {if $tplscope === 'box'}
            {block name='snippets-product-slider-box'}
                {card class="{if $tplscope === 'box'} box box-slider  mb-4{/if}{if isset($class) && $class|strlen > 0} {$class}{/if}" id="{if isset($id) && $id|strlen > 0}{$id}{/if}"}
                    {if !empty($title)}
                        {block name='snippets-product-slider-box-title'}
                            <div class="productlist-filter-headline">
                                {$title}
                                {if !empty($moreLink)}
                                    {link class="more float-right" href=$moreLink title=$moreTitle data-toggle="tooltip" data=["placement"=>"auto right"] aria=["label"=>"{$moreTitle}"]}
                                        <i class="fa fa-arrow-circle-right"></i>
                                    {/link}
                                {/if}
                            </div>
                        {/block}
                    {/if}
                    {block name='snippets-product-slider-box-products'}
                        <div class="mb-4 carousel carousel-arrows-inside {if $tplscope === 'box'}{block name='product-box-slider-class'}evo-box-slider{/block}{elseif $tplscope === 'half'}evo-slider-half{block name='product-slider-class'}evo-slider{/block}{/if}">
                            {foreach $productlist as $product}
                                {block name='snippets-product-slider-include-item-slider-box'}
                                    <div class="product-wrapper{if isset($style)} {$style}{/if}" {if isset($Link) && $Link->getLinkType() === $smarty.const.LINKTYP_STARTSEITE}itemprop="about"{else}itemprop="isRelatedTo"{/if} itemscope itemtype="http://schema.org/Product">
                                        {include file='productlist/item_slider.tpl' Artikel=$product tplscope=$tplscope class=''}
                                    </div>
                                {/block}
                            {/foreach}
                        </div>
                    {/block}
                {/card}
            {/block}
        {else}
            {block name='snippets-product-slider-other'}
                <div class="mb-5{if isset($class) && $class|strlen > 0} {$class}{/if}"{if isset($id) && $id|strlen > 0} id="{$id}"{/if}>
                    {if !empty($title)}
                        {block name='snippets-product-slider-other-title'}
                            <div class="hr-sect h2 mb-5">
                                {if !empty($moreLink)}
                                    {link class="text-decoration-none" href=$moreLink title=$moreTitle data-toggle="tooltip" data=["placement"=>"auto right"] aria=["label"=>$moreTitle]}
                                        {$title}
                                    {/link}
                                {else}
                                    {$title}
                                {/if}
                            </div>
                        {/block}
                    {/if}
                    {block name='snippets-product-slider-other-products'}
                        <div class="mb-4 carousel carousel-arrows-inside {block name='product-slider-class'}{if $tplscope === 'half'}evo-slider-half{else}evo-slider{/if}{/block}">
                            {foreach $productlist as $product}
                                {block name='snippets-product-slider-include-item-slider'}
                                    <div class="product-wrapper{if isset($style)} {$style}{/if}" {if isset($Link) && $Link->getLinkType() === $smarty.const.LINKTYP_STARTSEITE}itemprop="about"{else}itemprop="isRelatedTo"{/if} itemscope itemtype="http://schema.org/Product">
                                        {include file='productlist/item_slider.tpl' Artikel=$product tplscope=$tplscope class=''}
                                    </div>
                                {/block}
                            {/foreach}
                        </div>
                    {/block}
                </div>
            {/block}
        {/if}
    {/if}
    {/strip}
{/block}
