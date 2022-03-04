{block name='layout-header-categories'}
    <div id="mainNavigation" class="collapse navbar-collapse {if $menuMultipleRows|default:false}nav-multiple-row{else}nav-scrollbar{/if}">
        {block name='layout-header-categories-include-include-categories-header'}
            <div class="nav-mobile-header d-lg-none">
                {row class="align-items-center-util"}
                {col class="nav-mobile-header-toggler"}
                {block name='layout-header-categories-include-categories-mega-toggler'}
                    <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                {/block}
                {/col}
                {col class="col-auto nav-mobile-header-name ml-auto-util"}
                    <span class="nav-offcanvas-title">{lang key='menuName'}</span>
                {block name='layout-header-categories-include-categories-mega-back'}
                    {link href="#" class="nav-offcanvas-title d-none" data=["menu-back"=>""]}
                        <span class="fas fa-chevron-left icon-mr-2"></span>
                        <span>{lang key='back'}</span>
                    {/link}
                {/block}
                {/col}
                {/row}
                <hr class="nav-mobile-header-hr" />
            </div>
        {/block}
        {block name='layout-header-categories-include-include-categories-body'}
            <div class="nav-mobile-body">
                {navbarnav class="nav-scrollbar-inner mr-auto"}
                {if $menuScroll|default:false}
                    {block name='layout-header-jtl-header-include-include-categories-mega-home'}
                        <li class="nav-home-button nav-item nav-scrollbar-item d-none">
                            {link class="nav-link" href=$ShopURL title=$Einstellungen.global.global_shopname}
                                <span class="fas fa-home"></span>
                            {/link}
                        </li>
                    {/block}
                {/if}
                {block name='layout-header-categories-include-include-categories-mega'}
                    {include file='snippets/categories_mega.tpl'}
                {/block}
                {/navbarnav}
            </div>
        {/block}
    </div>
{/block}
