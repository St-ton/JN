{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-direct-purchase'}
    {card class="box box-direct-purchase mb-7" id="sidebox{$oBox->getID()}" title="{lang key='quickBuy'}"}
        {block name='boxes-box-direct-purchase-form'}
            {form class="top10" action="{get_static_route id='warenkorb.php'}" method="post"}
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
