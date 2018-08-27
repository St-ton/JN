<div id="evo-main-nav-wrapper" class="nav-wrapper{if $Einstellungen.template.theme.static_header === 'Y'} do-affix{/if}">
    <nav id="evo-main-nav" class="navbar navbar-default">
        <div class="container{if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}-fluid{/if}">
            <div class="navbar-header">
                <button type="button" class="btn btn-default navbar-toggle collapsed" data-toggle="collapse" data-target="#evo-navbar-collapse" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <i class="fa fa-bars"></i> {lang key='allCategories' section='global'}
                </button>
                <ul class="nav navbar-nav navbar-right force-float visible-xs visible-sm">
                    {if isset($smarty.session.Kunde) && isset($smarty.session.Kunde->kKunde) && $smarty.session.Kunde->kKunde > 0}
                        <li>
                            <a href="{get_static_route id='jtl.php'}?logout=1" title="{lang key='logOut'}">
                                <span class="fa fa-sign-out"></span>
                            </a>
                        </li>
                    {/if}
                    <li>
                        <a href="{get_static_route id='jtl.php'}" title="{lang key='myAccount'}">
                            <span class="fa fa-user"></span>
                        </a>
                    </li>
                    <li>
                        <a href="{get_static_route id='warenkorb.php'}" title="{lang key='basket'}">
                            <span class="fa fa-shopping-cart"></span>
                            {if $WarenkorbArtikelPositionenanzahl >= 1}
                                <sup class="badge">
                                    <em>{$WarenkorbArtikelPositionenanzahl}</em>
                                </sup>
                            {/if}
                        </a>
                    </li>
                </ul>
            </div>

            <div class="megamenu collapse navbar-collapse" id="evo-navbar-collapse"  data-active-tab="1">
                <ul class="nav navbar-nav">
                    {include file='snippets/categories_mega.tpl'}
                    <span class="TabNav_Indicator"></span>
                </ul>
                {if $Einstellungen.template.theme.static_header === 'Y'}
                    <ul class="nav navbar-nav navbar-right visible-affix hidden-xs hidden-sm">
                        <li class="cart-menu dropdown bs-hover-enabled {if $nSeitenTyp == 3} current{/if}" data-toggle="basket-items">
                            {include file='basket/cart_dropdown_label.tpl'}
                        </li>
                    </ul>
                {/if}
            </div><!-- /.navbar-collapse -->
        </div><!-- /.container(-fluid) -->
    </nav>
</div>