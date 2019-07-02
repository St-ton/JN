{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-productlist-page-nav'}
    {if $Suchergebnisse->getProductCount() > 0}
        {include file='snippets/opc_mount_point.tpl' id='opc_before_page_nav_'|cat:$navid}
        {row class="no-gutters productlist-page-nav"}
            {if count($NaviFilter->getSearchResults()->getProducts()) > 0}
                {block name='snippets-productlist-page-nav-result-options-sort'}
                    {col cols=12 md="auto" class="displayoptions form-inline d-flex justify-content-between mb-3 mb-md-0"}
                        {dropdown class="filter-type-FilterItemSort btn-group" variant="light" text="{lang key='sorting' section='productOverview'}"}
                            {foreach $Suchergebnisse->getSortingOptions() as $option}
                                {dropdownitem rel="nofollow" href=$option->getURL() class="filter-item" active=$option->isActive()}
                                    {$option->getName()}
                                {/dropdownitem}
                            {/foreach}
                        {/dropdown}
                        {dropdown class="filter-type-FilterItemLimits btn-group ml-2" variant="light" text="{lang key='productsPerPage' section='productOverview'}"}
                            {foreach $Suchergebnisse->getLimitOptions() as $option}
                                {dropdownitem rel="nofollow" href=$option->getURL() class="filter-item" active=$option->isActive()}
                                    {$option->getName()}
                                {/dropdownitem}
                            {/foreach}
                        {/dropdown}
                        {if isset($oErweiterteDarstellung->nDarstellung) && $Einstellungen.artikeluebersicht.artikeluebersicht_erw_darstellung === 'Y' && empty($AktuelleKategorie->categoryFunctionAttributes['darstellung'])}
                            {buttongroup class="ml-2"}
                                {link href=$oErweiterteDarstellung->cURL_arr[$smarty.const.ERWDARSTELLUNG_ANSICHT_LISTE]
                                    id="ed_list"
                                    class="btn btn-light btn-option ed list{if $oErweiterteDarstellung->nDarstellung === $smarty.const.ERWDARSTELLUNG_ANSICHT_LISTE} active{/if}"
                                    role="button"
                                    title="{lang key='list' section='productOverview'}"
                                }
                                    <span class="fa fa-square"></span>
                                {/link}
                                {link href=$oErweiterteDarstellung->cURL_arr[$smarty.const.ERWDARSTELLUNG_ANSICHT_GALERIE]
                                    id="ed_gallery"
                                    class="btn btn-light btn-option ed gallery{if $oErweiterteDarstellung->nDarstellung === $smarty.const.ERWDARSTELLUNG_ANSICHT_GALERIE} active{/if}"
                                    role="button"
                                    title="{lang key='gallery' section='productOverview'}"
                                }
                                    <span class="fa fa-th-large"></span>
                                {/link}
                            {/buttongroup}
                        {/if}
                    {/col}
                {/block}
            {/if}
            {block name='snippets-productlist-page-nav-current-page-count'}
                {col cols="auto" class="ml-auto productlist-item-info d-none d-md-flex"}
                    {lang key="products"} {$Suchergebnisse->getOffsetStart()} - {$Suchergebnisse->getOffsetEnd()} {lang key='of' section='productOverview'} {$Suchergebnisse->getProductCount()}
                {/col}
            {/block}
            {if $Suchergebnisse->getPages()->getMaxPage() > 1}
                {block name='snippets-productlist-page-nav-page-nav'}
                    {col cols=12 md="auto" class="productlist-pagination"}
                        <nav class="navbar-pagination" aria-label="Productlist Navigation">
                            <ul class="pagination">
                                {block name='snippets-productlist-page-nav-first-page'}
                                    <li class="page-item{if $Suchergebnisse->getPages()->getCurrentPage() == 1} disabled{/if}">
                                        {link class="page-link" href=$filterPagination->getPrev()->getURL()}<span aria-hidden="true">&#8592;</span>{/link}
                                    </li>
                                {/block}
                                <li class="page-item dropdown">
                                    <button type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="pagination-site">Seite</span> {$Suchergebnisse->getPages()->getCurrentPage()}
                                    </button>
                                    <div class="dropdown-menu">
                                        {block name='snippets-productlist-page-nav-pages'}
                                            {foreach $filterPagination->getPages() as $page}
                                                <div class="dropdown-item page-item{if $page->isActive()} active{/if}">
                                                    {link class="page-link" href=$page->getURL()}<span class="pagination-site">Seite</span> {$page->getPageNumber()}{/link}
                                                </div>
                                            {/foreach}
                                        {/block}
                                    </div>
                                </li>
                                {block name='snippets-productlist-page-nav-last-page'}
                                    <li class="page-item{if $Suchergebnisse->getPages()->getCurrentPage() == $Suchergebnisse->getPages()->getMaxPage()} disabled{/if}">
                                        {link class="page-link" href=$filterPagination->getNext()->getURL()}<span aria-hidden="true">&#8594;</span>{/link}
                                    </li>
                                {/block}
                            </ul>
                        </nav>
                    {/col}
                {/block}
            {/if}
        {/row}
    {/if}
{/block}
