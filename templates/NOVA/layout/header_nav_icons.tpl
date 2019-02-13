{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{navitem tag="div" class="btn-link d-flex d-md-none" aria=["expanded"=>false] data=["toggle"=>"collapse", "target"=>"#nav-search-collapse"]}
    <i class="fas fa-search"></i>
{/navitem}

{include file='layout/header_shop_nav_compare.tpl'}

{include file='layout/header_shop_nav_wish.tpl'}

{navitem tag="div" class="btn-link" aria=["expanded"=>false] data=["toggle"=>"collapse", "target"=>"#nav-account-collapse"]}
    <i class="fas fa-user"></i>
{/navitem}

{include file='basket/cart_dropdown_label.tpl'}








{*{include file='layout/header_shop_nav_wish.tpl'}
{if isset($smarty.session.Kunde) && isset($smarty.session.Kunde->kKunde) && $smarty.session.Kunde->kKunde > 0}
    {navitem tag="div" router-tag="a" href="{get_static_route id='jtl.php'}?logout=1" title="{lang key='logOut'}" class="d-flex d-md-none"}
        <span class="fa fa-sign-out-alt"></span>
    {/navitem}
{/if}*}
{*{navitem tag="div" router-tag="a" href="{get_static_route id='jtl.php'}" title="{lang key='myAccount'}" class="d-flex d-md-none{if $nSeitenTyp === $smarty.const.PAGE_MEINKONTO} active{/if}"}
    <i class="fa fa-user"></i>
{/navitem}*}

{*

<div id="navbarToggler" class="collapse navbar-collapse" data-parent="#evo-main-nav-wrapper">
    {navbarnav class="megamenu show"}
        {include file='snippets/categories_mega.tpl'}
        <span class="TabNav_Indicator"></span>
    {/navbarnav}
</div>
*}
