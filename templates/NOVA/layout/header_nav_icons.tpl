{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='layout-header-nav-icons'}
    {block name='layout-header-nav-icons-search'}
        {navitem tag="div" class="d-flex d-md-none mr-2" aria=['expanded' => 'false'] role='button'
            data=['toggle' => 'collapse', 'target' => '#nav-search-collapse'] router-tag='div'}
            <i class="fas fa-search"></i>
        {/navitem}
    {/block}
    {block name='layout-header-nav-icons-login'}
        {navitem tag="div" class="mr-2 mr-md-3" aria=['expanded' => 'false'] data=['toggle' => 'collapse', 'target' => '#nav-account-collapse']}
            <span class="fas fa-user mr-md-3"></span>
            {if empty($smarty.session.Kunde->kKunde)}
                <span class="d-none d-md-inline-block">{lang key='login'}</span>
            {else}
                <span class="d-none d-md-inline-block">{lang key='hello'}, {$smarty.session.Kunde->cVorname} {$smarty.session.Kunde->cNachname}</span>
            {/if}
        {/navitem}
    {/block}
    {block name='layout-header-nav-icons-include-header-shop-nav-compare'}
        {include file='layout/header_shop_nav_compare.tpl'}
    {/block}
    {block name='layout-header-nav-icons-include-header-shop-nav-wish'}
        {include file='layout/header_shop_nav_wish.tpl'}
    {/block}
    {block name='layout-header-nav-icons-include-header-shop-nav-account'}
        {include file='layout/header_shop_nav_account.tpl'}
    {/block}
    {block name='layout-header-nav-icons-include-cart-dropdown-label'}
        <div class="cart-icon-dropdown">
            {include file='basket/cart_dropdown_label.tpl'}
        </div>
    {/block}
{/block}
