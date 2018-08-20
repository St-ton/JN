{extends file="{$parent_template_path}/layout/header.tpl"}

{block name='header'}
    {if Shop::isAdmin()}
        {include file='layout/header_composer_menu.tpl'}
    {/if}
    <header class="hidden-print {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'fluid'}container-block{/if}{if $Einstellungen.template.theme.static_header === 'Y'} fixed-navbar{/if}" id="evo-nav-wrapper">
        <div class="container">
            {block name='header-container-inner'}
                {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}
                    <div class="container-block clearfix">
                {/if}
                <div id="header-branding" >
                    {block name='header-branding'}
                        {block name='header-branding-top-bar'}
                            <div id="top-bar" class="text-right hidden-xs clearfix">
                                {include file='layout/header_top_bar.tpl'}
                            </div>
                        {/block}
                        {block name='header-branding-content'}
                            <div class="row">
                                <div class="col-xs-4" id="logo" itemprop="publisher" itemscope itemtype="http://schema.org/Organization" itemid="">
                                    {block name='logo'}
                                        <span itemprop="name" class="hidden">{$meta_publisher}</span>
                                        <meta itemprop="url" content="{$ShopURL}">
                                        <meta itemprop="logo" content="{$imageBaseURL}{$ShopLogoURL}">
                                        <a href="{$ShopURL}" title="{$Einstellungen.global.global_shopname}">
                                            {if isset($ShopLogoURL)}
                                                {image src=$ShopLogoURL alt=$Einstellungen.global.global_shopname class="img-responsive"}
                                            {else}
                                                <span class="h1">{$Einstellungen.global.global_shopname}</span>
                                            {/if}
                                        </a>
                                    {/block}
                                </div>
                                <div class="col-xs-8" id="shop-nav">
                                    {block name='header-branding-shop-nav'}
                                        {include file='layout/header_shop_nav.tpl'}
                                    {/block}
                                </div>
                            </div>
                        {/block}
                    {/block}{* /header-branding *}
                </div>
                {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}
                    </div>{* /container-block *}
                {/if}
            {/block}
        </div>{* /container *}
        {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}
        <div class="container">
            {/if}

            {block name='header-category-nav'}
                <div class="category-nav navbar-wrapper">
                {include file='layout/header_category_nav.tpl'}
                </div>{* /category-nav *}
            {/block}


            {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}
        </div>{* /container-block *}
        {/if}
    </header>
{/block}