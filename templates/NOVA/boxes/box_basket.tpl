{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-basket'}
    {card
        class="box box-basket mb-4"
        id="sidebox{$oBox->getID()}"
    }
        <div class="box-body text-center">
            {block name='boxes-box-basket-content'}
                {block name='boxes-box-last-seen-title'}
                    <div class="productlist-filter-headline align-items-center d-flex">
                        <span>{lang key='yourBasket'}</span>
                        <span id='basket_loader'></span>
                    </div>
                {/block}
                {link href="{get_static_route id='warenkorb.php'}" class="basket" id="basket_drag_area"}
                    <span id="basket_text">{$Warenkorbtext}</span><br>
                    <span class="basket_link"><i class="fas fa-shopping-cart"></i> {lang key='gotoBasket'}</span>
                {/link}
            {/block}
        </div>
    {/card}
{/block}
