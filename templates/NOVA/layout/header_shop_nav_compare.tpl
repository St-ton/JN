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
        {dropdownitem tag="div" right=true id="comparelist-dropdown-container"}
            {row}
                {col id='comparelist-dropdown-content'}
                    {include file='snippets/comparelist_dropdown.tpl'}
                {/col}
            {/row}
            {row class="mt-2"}
                {col}
                    {link class='btn btn-sm btn-primary float-right' href="{get_static_route id='vergleichsliste.php'}"}
                        {lang key='gotToCompare'}
                    {/link}
                {/col}
            {/row}
        {/dropdownitem}
    {/collapse}
</div>