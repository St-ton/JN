{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='layout-header-nav-icons'}
    {block name='layout-header-nav-icons-search'}
        {*{navitem tag="li" class="d-flex d-md-none mr-1" aria=['expanded' => 'false', 'label' => {lang key='search'}] role='button'*}
            {*data=['toggle' => 'collapse', 'target' => '#nav-search-collapse'] router-tag='div'}*}
            {*<i class="fas fa-search"></i>*}
        {*{/navitem}*}
    {/block}
    {block name='layout-header-nav-icons-login'}
        {navitemdropdown
            tag="li"
            aria=['expanded' => 'false']
            no-caret=true
            right=true
            text='<span class="fas fa-user"></span>'}
            {block name='layout-header-nav-icons-include-header-shop-nav-account'}
                {include file='layout/header_shop_nav_account.tpl'}
            {/block}
        {/navitemdropdown}
    {/block}
    {block name='layout-header-nav-icons-include-header-shop-nav-compare'}
        {include file='layout/header_shop_nav_compare.tpl'}
    {/block}
    {block name='layout-header-nav-icons-include-header-shop-nav-wish'}
        {include file='layout/header_shop_nav_wish.tpl'}
    {/block}

    {block name='layout-header-nav-icons-include-cart-dropdown-label'}
        {include file='basket/cart_dropdown_label.tpl'}
    {/block}
{/block}
