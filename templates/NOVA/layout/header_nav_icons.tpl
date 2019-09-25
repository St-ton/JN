{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='layout-header-nav-icons'}
    {block name='layout-header-nav-icons-search'}
        <li class="nav-item d-none d-lg-block">
            <div class="form-icon-trigger">
                {input id="search-header" name="qs" type="text" class="ac_input" placeholder="{lang key='search'}" autocomplete="off" aria=["label"=>"{lang key='search'}"]}
                <label class="form-icon-trigger-label" for="search-header"><span class="fas fa-search"></span></label>
            </div>
        </li>
    {/block}
    {block name='layout-header-nav-icons-search-dropdown'}
        {navitemdropdown class='d-block d-lg-none' text='<i class="fas fa-search"></i>' right=true no-caret=true}
            <div class="dropdown-body">
                {input name="qs" type="text" class="ac_input w-100" placeholder="{lang key='search'}" autocomplete="off" aria=["label"=>"{lang key='search'}"]}
                {button class="mt-3" type="submit" size="sm" variant="primary" block=true name="search" id="search-submit-button" aria=["label"=>"{lang key='search'}"]}
                    {lang key='search'}
                {/button}
            </div>
        {/navitemdropdown}
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
