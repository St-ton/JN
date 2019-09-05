{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='basket-cart-dropdown-label'}
    {navitem tag="div" aria=['expanded' => 'false'] data=['toggle' => 'collapse', 'target' => '#nav-cart-collapse']}
        <i class='fas fa-shopping-cart{if $WarenkorbArtikelPositionenanzahl == 0} mr-3{/if} position-relative'>
            {if $WarenkorbArtikelPositionenanzahl >= 1}
            <span class="fa-sup" title="{$WarenkorbArtikelPositionenanzahl}">
                {$WarenkorbArtikelPositionenanzahl}
            </span>
            {/if}
        </i>
        <span class="d-none d-md-block {if $WarenkorbArtikelPositionenanzahl != 0}ml-3{/if}">{$WarensummeLocalized[0]}</span>
    {/navitem}
    {block name='basket-cart-dropdown-label-include-cart-dropdown'}
        {include file='basket/cart_dropdown.tpl'}
    {/block}
{/block}
