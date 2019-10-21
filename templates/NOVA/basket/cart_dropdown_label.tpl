{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='basket-cart-dropdown-label'}
    <li class="cart-icon-dropdown nav-item dropdown">
        {block name='basket-cart-dropdown-label-link'}
            {link class='nav-link' aria=['expanded' => 'false'] data=['toggle' => 'dropdown']}
                {block name='basket-cart-dropdown-label-count'}
                    <i class='fas fa-shopping-cart{if $WarenkorbArtikelPositionenanzahl == 0} mr-md-3{/if} position-relative'>
                        {if $WarenkorbArtikelPositionenanzahl >= 1}
                        <span class="fa-sup" title="{$WarenkorbArtikelPositionenanzahl}">
                            {$WarenkorbArtikelPositionenanzahl}
                        </span>
                        {/if}
                    </i>
                {/block}
                {block name='basket-cart-dropdown-labelprice'}
                    <span class="text-nowrap d-none d-md-inline-block font-size-base {if $WarenkorbArtikelPositionenanzahl != 0}ml-3{/if}">{$WarensummeLocalized[0]}</span>
                {/block}
            {/link}
        {/block}
        {block name='basket-cart-dropdown-label-include-cart-dropdown'}
            {include file='basket/cart_dropdown.tpl'}
        {/block}
    </li>
{/block}
