{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{navitem tag="div" aria=['expanded' => 'false'] data=['toggle' => 'collapse', 'target' => '#nav-cart-collapse']}
    <i class='fas fa-shopping-cart'></i>
{if $WarenkorbArtikelPositionenanzahl >= 1}
    <sup>{badge pill=true variant='primary'}{$WarenkorbArtikelPositionenanzahl}{/badge}</sup>
{/if}
{/navitem}
{include file='basket/cart_dropdown.tpl'}
