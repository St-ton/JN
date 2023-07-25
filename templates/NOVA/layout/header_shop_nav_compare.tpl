{block name='layout-header-shop-nav-compare'}
    {$productCount = count(JTL\Session\Frontend::getCompareList()->oArtikel_arr)}
    <li id="shop-nav-compare"
        title="{lang key='compare'}"
        class="nav-item dropdown{if $nSeitenTyp === $smarty.const.PAGE_VERGLEICHSLISTE} active{/if} {if $productCount === 0}d-none{/if}">
        {block name='layout-header-shop-nav-compare-link'}
            {link class='nav-link' data=['toggle'=>'dropdown'] aria=['haspopup'=>'true', 'expanded'=>'false', 'label'=>{lang key='compare'}]}
                <i class="fas fa-list">
                    <span id="comparelist-badge" class="fa-sup"
                          title="{$productCount}">
                        {$productCount}
                    </span>
                </i>
            {/link}
        {/block}
        {block name='layout-header-shop-nav-compare-dropdown'}
            <div id="comparelist-dropdown-container" class="dropdown-menu dropdown-menu-right lg-min-w-lg">
                <div id='comparelist-dropdown-content'>
                    {block name='layout-header-shop-nav-compare-include-comparelist-dropdown'}
                        {include file='snippets/comparelist_dropdown.tpl'}
                    {/block}
                </div>
            </div>
        {/block}
    </li>
{/block}
