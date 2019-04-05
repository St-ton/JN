{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='basket-cart-dropdown-label'}
    {navitem tag="div" aria=['expanded' => 'false'] data=['toggle' => 'collapse', 'target' => '#nav-cart-collapse']}
        <i class='fas fa-shopping-cart{if $WarenkorbArtikelPositionenanzahl == 0} mr-3{/if}'></i>
        {if $WarenkorbArtikelPositionenanzahl >= 1}
            <sup class="mr-md-2">{badge pill=true variant='primary'}{$WarenkorbArtikelPositionenanzahl}{/badge}</sup>
        {/if}
        <span class="d-none d-md-block">{$WarensummeLocalized[0]}</span>
    {/navitem}
    {block name='basket-cart-dropdown-label-include-cart-dropdown'}
        {include file='basket/cart_dropdown.tpl'}
    {/block}
{/block}
