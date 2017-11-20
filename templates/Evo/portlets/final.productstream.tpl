{if $productlist|@count > 0}
    <div id="result-wrapper"> {*muss ne klasse werden, da mehrere auf einer seite sein können*}
        <div class="row {if $style !== 'list'}row-eq-height row-eq-img-height{/if} {$style}" id="product-list">
            {foreach name=artikel from=$productlist item=Artikel}
                <div class="product-wrapper {$grid}">
                    <meta itemprop="position" content="{$smarty.foreach.artikel.iteration}">
                    {if $style === 'list'}
                        {include file='../productlist/item_list.tpl' tplscope=$style}
                    {else}
                        {include file='../productlist/item_box.tpl' tplscope=$style class='thumbnail'}
                    {/if}
                </div>
            {/foreach}
        </div>
    </div>

{/if}