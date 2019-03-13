{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<div class="comparelist-icon-dropdown">
    {navitem
        id="shop-nav-compare"
        tag="div"
        title="{lang key='compare'}"
        class="{if $nSeitenTyp === $smarty.const.PAGE_VERGLEICHSLISTE} active{/if} {if empty($smarty.session.Vergleichsliste->oArtikel_arr)}d-none{/if}"
        data=['toggle' => 'collapse', 'target' => '#nav-comparelist-collapse']
    }
        <span class="fas fa-tasks"></span>
        <sup>
            {badge pill=true variant="primary" id='comparelist-badge'}
                {if !empty($smarty.session.Vergleichsliste->oArtikel_arr)}{$smarty.session.Vergleichsliste->oArtikel_arr|count}{/if}
            {/badge}
        </sup>
    {/navitem}
    {collapse id="nav-comparelist-collapse" tag="div"  data=["parent"=>"#evo-main-nav-wrapper"] class="mt-md-2"}
        <div id="comparelist-dropdown-container" class="p-3">
            {row}
                {col id='comparelist-dropdown-content'}
                    {include file='snippets/comparelist_dropdown.tpl'}
                {/col}
            {/row}
            {row class="mt-2"}
                {col}
                {if !empty($smarty.session.Vergleichsliste->oArtikel_arr) && count($smarty.session.Vergleichsliste->oArtikel_arr) <= 1}
                    {lang key='productNumberHint' section='comparelist'}
                {else}
                    {link
                        class="btn btn-sm btn-primary float-right"
                        id='nav-comparelist-goto'
                        href="{get_static_route id='vergleichsliste.php'}"
                    }
                        {lang key='gotToCompare'}
                    {/link}
                {/if}
                {/col}
            {/row}
        </div>
    {/collapse}
</div>
