{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{card
    class="box box-basket mb-7"
    id="sidebox{$oBox->getID()}"
    title="{lang key='yourBasket'}<span id='basket_loader'></span>"
}
    <hr class="mt-0 mb-4">
    <div class="box-body text-center">
        {link href="{get_static_route id='warenkorb.php'}" class="basket" id="basket_drag_area"}
            <span id="basket_text">{$Warenkorbtext}</span><br>
            <span class="basket_link"><i class="fas fa-shopping-cart"></i> {lang key='gotoBasket'}</span>
        {/link}
    </div>
{/card}
