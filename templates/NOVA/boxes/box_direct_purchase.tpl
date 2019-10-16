{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-direct-purchase'}
    {card class="box box-direct-purchase mb-4" id="sidebox{$oBox->getID()}" title=""}
        {block name='boxes-box-direct-purchase-title'}
            <div class="productlist-filter-headline">
                <span>{lang key='quickBuy'}</span>
            </div>
        {/block}
        {block name='boxes-box-direct-purchase-form'}
            {form action="{get_static_route id='warenkorb.php'}" method="post"}
                {input type="hidden" name="schnellkauf" value="1"}
                {inputgroup}
                    {input aria=["label"=>"{lang key='quickBuy'}"] type="text" placeholder="{lang key='productNoEAN'}"
                           name="ean" id="quick-purchase"}
                    {inputgroupaddon append=true}
                        {button type="submit" title="{lang key='intoBasket'}"}
                            <span class="fas fa-shopping-cart"></span>
                        {/button}
                    {/inputgroupaddon}
                {/inputgroup}
            {/form}
        {/block}
    {/card}
{/block}
