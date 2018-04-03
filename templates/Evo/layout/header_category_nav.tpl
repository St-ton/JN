<div id="evo-main-nav-wrapper" class="nav-wrapper{if $Einstellungen.template.theme.static_header === 'Y'} do-affix{/if}">
    <nav id="evo-main-nav" class="navbar navbar-default{*{if $Einstellungen.template.theme.static_header === 'Y'} navbar-fixed-top{/if}*}">
        <div class="container{if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}-fluid{/if}">
            <div class="navbar-header">
                <button type="button" class="btn btn-default navbar-toggle collapsed" data-toggle="collapse" data-target="#evo-navbar-collapse" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <i class="fa fa-bars"></i> {lang key="allCategories" section="global"}
                </button>
            </div>

            <div class="megamenu collapse navbar-collapse" id="evo-navbar-collapse">
                <ul class="nav navbar-nav">
                    {include file='snippets/categories_mega.tpl'}
                </ul>
                <ul class="nav navbar-nav navbar-right{if $Einstellungen.template.theme.static_header === 'N'} visible-xs visible-sm{/if}">
                    <li class="cart-menu dropdown bs-hover-enabled {if $nSeitenTyp == 3} current{/if}" data-toggle="basket-items">
                        {include file='basket/cart_dropdown_label.tpl'}
                    </li>
                </ul>
            </div><!-- /.navbar-collapse -->
        </div><!-- /.container(-fluid) -->
    </nav>
</div>