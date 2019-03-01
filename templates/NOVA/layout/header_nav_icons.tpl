{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{navitem tag="div" class="btn-link d-flex d-md-none" aria=['expanded' => 'false'] data=['toggle' => 'collapse', 'target' => '#nav-search-collapse']}
    <i class="fas fa-search"></i>
{/navitem}
{include file='layout/header_shop_nav_compare.tpl'}
{include file='layout/header_shop_nav_wish.tpl'}
{navitem tag="div" class="btn-link" aria=['expanded' => 'false'] data=['toggle' => 'collapse', 'target' => '#nav-account-collapse']}
    <i class="fas fa-user"></i>
{/navitem}
{include file='layout/header_shop_nav_account.tpl'}
<div class="cart-icon-dropdown">
    {include file='basket/cart_dropdown_label.tpl'}
</div>