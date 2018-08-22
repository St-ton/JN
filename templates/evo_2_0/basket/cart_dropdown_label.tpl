{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $WarenkorbArtikelPositionenanzahl >= 1}
<a href="{get_static_route id='warenkorb.php'}" class="dropdown-toggle" data-toggle="dropdown" title="{lang key='basket'}">
            <sup class="badge">
                <em>{$WarenkorbArtikelPositionenanzahl}</em>
            </sup>
        <span class="shopping-cart-label hidden-sm"> {$WarensummeLocalized[$NettoPreise]}</span> <span class="caret"></span>
    </a>
    <ul class="cart-dropdown dropdown-menu dropdown-menu-right">
        {include file='basket/cart_dropdown.tpl'}
    </ul>
{/if}
