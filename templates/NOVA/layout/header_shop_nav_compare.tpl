{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='layout-header-shop-nav-compare'}
    <div class="comparelist-icon-dropdown">
        {navitem
            id="shop-nav-compare"
            tag="div"
            title="{lang key='compare'}"
            class="mr-2{if $nSeitenTyp === $smarty.const.PAGE_VERGLEICHSLISTE} active{/if} {if empty($smarty.session.Vergleichsliste->oArtikel_arr)}d-none{/if}"
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
                {row id='comparelist-dropdown-content'}
                    {block name='layout-header-shop-nav-compare-include-comparelist-dropdown'}
                        {include file='snippets/comparelist_dropdown.tpl'}
                    {/block}
                {/row}
            </div>
        {/collapse}
    </div>
{/block}
