{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{strip}
{if $productlist|@count > 0}
    {if !isset($tplscope)}
        {assign var='tplscope' value='slider'}
    {/if}

    {if $tplscope === 'box'}
        {card class="{if $tplscope === 'box'} box box-slider  mb-7{/if}{if isset($class) && $class|strlen > 0} {$class}{/if}" id="{if isset($id) && $id|strlen > 0}{$id}{/if}"}
        {if $title|strlen > 0}
            <div class="h4 card-title">
                {$title}
                {if !empty($moreLink)}
                    {link class="more float-right" href="{$moreLink}" title="{$moreTitle}" data-toggle="tooltip" data=["placement"=>"auto right"] aria=["label"=>"{$moreTitle}"]}
                        <i class="fa fa-arrow-circle-right"></i>
                    {/link}
                {/if}
            </div>
        {/if}
            <hr class="mt-0 mb-4">
            <div class="mb-4 {if $tplscope === 'box'}{block name='product-box-slider-class'}evo-box-slider{/block}{else}{block name='product-slider-class'}evo-slider{/block}{/if}">
                {foreach $productlist as $product}
                    <div class="product-wrapper float-left{if isset($style)} {$style}{/if}" {if isset($Link) && $Link->getLinkType() === $smarty.const.LINKTYP_STARTSEITE}itemprop="about"{else}itemprop="isRelatedTo"{/if} itemscope itemtype="http://schema.org/Product">
                        {include file='productlist/item_slider.tpl' Artikel=$product tplscope=$tplscope class=''}
                    </div>
                {/foreach}
            </div>
        {/card}
    {else}
        <div class="mb-5{if isset($class) && $class|strlen > 0}{$class}{/if}"{if isset($id) && $id|strlen > 0} id="{$id}"{/if}>
            {if $title|strlen > 0}
                <div class="hr-sect my-4">
                    {if !empty($moreLink)}
                        {link class="more float-right" href="{$moreLink}" title="{$moreTitle}" data-toggle="tooltip" data=["placement"=>"auto right"] aria=["label"=>"{$moreTitle}"]}
                        {$title}
                        {/link}
                    {/if}
                </div>
            {/if}
            <div class="mb-4 {block name='product-slider-class'}evo-slider{/block}">
                {foreach $productlist as $product}
                    <div class="product-wrapper float-left{if isset($style)} {$style}{/if}" {if isset($Link) && $Link->getLinkType() === $smarty.const.LINKTYP_STARTSEITE}itemprop="about"{else}itemprop="isRelatedTo"{/if} itemscope itemtype="http://schema.org/Product">
                        {include file='productlist/item_slider.tpl' Artikel=$product tplscope=$tplscope class=''}
                    </div>
                {/foreach}
            </div>
        </div>
    {/if}
{/if}
{/strip}
