{block name='layout-header-menu-single-row'}
    {$menuScroll=$Einstellungen.template.header.menu_scroll === 'menu' && $Einstellungen.template.header.menu_single_row === 'Y'}
    {block name='layout-header-menu-single-row-main'}
        {block name='layout-header-menu-single-row-top-bar-outer'}
            {if $menuScroll && $Einstellungen.template.header.menu_show_topbar === 'Y'}
                <div id="header-top-bar" class="d-none topbar-wrapper full-width-mega {if $Einstellungen.template.header.header_full_width === 'Y'}is-fullwidth{/if} {if $nSeitenTyp !== $smarty.const.PAGE_BESTELLVORGANG}d-lg-flex{/if}">
                    <div class="container-fluid {if $Einstellungen.template.header.header_full_width === 'N'}container-fluid-xl{/if} {if $nSeitenTyp !== $smarty.const.PAGE_BESTELLVORGANG}d-lg-flex flex-row-reverse{/if}">
                        {block name='layout-header-menu-single-row-top-bar-outer-include-header-top-bar'}
                            {include file='layout/header_top_bar.tpl'}
                        {/block}
                    </div>
                </div>
            {/if}
        {/block}
        {block name='layout-header-menu-single-row-nav'}
            {block name='layout-header-menu-single-row-nav-main'}
                <div class="container-fluid hide-navbar {if $Einstellungen.template.header.header_full_width === 'N'}container-fluid-xl{/if}
                            menu-search-position-{$Einstellungen.template.header.menu_search_position}">
                    {navbar toggleable=true fill=true type="expand-lg" class="row justify-content-center align-items-center-util"}
                        {block name='layout-header-menu-single-row-logo'}
                            {col class="col-lg-auto nav-logo-wrapper {if $Einstellungen.template.header.menu_logo_centered === 'Y'}order-lg-2 m-lg-auto{else}order-lg-1{/if}"}
                                {block name='layout-header-menu-single-row-logo-include-header-logo'}
                                    {include file='layout/header_logo.tpl'}
                                {/block}
                            {/col}
                        {/block}
                        {block name='layout-header-menu-single-row-nav-main-inner'}
                            {if $nSeitenTyp === $smarty.const.PAGE_BESTELLVORGANG}
                                {block name='layout-header-menu-single-row-secure-checkout'}
                                    {block name='layout-header-menu-single-row-secure-checkout-title'}
                                        {col class="secure-checkout-icon"}
                                            <i class="fas fa-lock icon-mr-2"></i>{lang key='secureCheckout' section='checkout'}
                                        {/col}
                                    {/block}
                                    {block name='layout-header-menu-single-row-top-bar-inner'}
                                        {col class="secure-checkout-topbar col-auto ml-auto-util d-none d-lg-block"}
                                            {include file='layout/header_top_bar.tpl'}
                                        {/col}
                                    {/block}
                                {/block}
                            {else}
                                {block name='layout-header-menu-single-row-include-header-nav-search'}
                                    {col class="main-search-wrapper nav-right {if $Einstellungen.template.header.menu_logo_centered === 'Y'}order-lg-1{else}order-lg-2{/if}"}
                                        {include file='layout/header_nav_search.tpl'}
                                    {/col}
                                {/block}
                                {block name='layout-header-menu-single-row-icons'}
                                    {col class="col-auto nav-icons-wrapper order-lg-3"}
                                        {include file='layout/header_nav_icons.tpl'}
                                    {/col}
                                {/block}
                            {/if}
                        {/block}
                    {/navbar}
                </div>
            {/block}
            {block name='layout-header-menu-single-row-nav-categories'}
                {if $nSeitenTyp !== $smarty.const.PAGE_BESTELLVORGANG}
                    <div class="container-fluid {if $Einstellungen.template.header.header_full_width === 'N'}container-fluid-xl{/if}
                        menu-center-{$Einstellungen.template.header.menu_center}
                        menu-multiple-rows-{$Einstellungen.template.header.menu_multiple_rows}">
                        {navbar toggleable=true fill=true type="expand-lg" class="justify-content-start {if $nSeitenTyp === $smarty.const.PAGE_BESTELLVORGANG}align-items-center-util{else}align-items-lg-end{/if}"}
                            {block name='layout-header-menu-single-row-include-categories-mega'}
                                {include file='layout/header_categories.tpl'
                                    menuMultipleRows=($Einstellungen.template.header.menu_multiple_rows === 'multiple')
                                    menuScroll=$menuScroll}
                            {/block}
                        {/navbar}
                    </div>
                {/if}
            {/block}
        {/block}
    {/block}
    {block name='layout-header-menu-single-row-scripts'}
        {if $menuScroll}
            {inline_script}
                <script>
                    let lastScroll = 0,
                        timeoutSc,
                        $navbar = $('.hide-navbar'),
                        $topbar = $('#header-top-bar'),
                        $home   = $('.nav-home-button'),
                        scrollTopActive = false;
                    $(document).on('scroll wheel', function (e) {
                        if (window.innerWidth < globals.breakpoints.lg || $('.secure-checkout-topbar').length) {
                            return;
                        }
                        window.clearTimeout(timeoutSc);
                        timeoutSc = window.setTimeout(function () {
                            let newScroll = $(this).scrollTop();
                            if (newScroll < lastScroll || $(window).scrollTop() === 0) {
                                if ($(window).scrollTop() === 0 && (lastScroll > 100 || e.type === 'wheel' || scrollTopActive)) {
                                    $topbar.addClass('d-lg-flex');
                                    $navbar.removeClass('d-none');
                                    $home.removeClass('d-lg-block');
                                    scrollTopActive = false;
                                } else {
                                    $topbar.removeClass('d-lg-flex');
                                    $navbar.addClass('d-none');
                                    $home.addClass('d-lg-block');
                                }
                            } else {
                                $topbar.removeClass('d-lg-flex');
                                $navbar.addClass('d-none');
                                $home.addClass('d-lg-block');
                            }
                            lastScroll = newScroll;
                        }, 20);
                    });
                    $('.smoothscroll-top').on('click', function () {
                        scrollTopActive = true;
                    });
                </script>
            {/inline_script}
        {/if}
    {/block}
{/block}
