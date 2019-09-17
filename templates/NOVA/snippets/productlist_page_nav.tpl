{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-productlist-page-nav'}
    {if $Suchergebnisse->getProductCount() > 0}
        {opcMountPoint id='opc_before_page_nav_'|cat:$navid}

        {if $hrTop|default:false === true}
            {block name='snippets-productlist-page-nav-hr-top'}
                <hr>
            {/block}
        {/if}
        {row class="{if $navid === 'header'}mt-6{/if} no-gutters productlist-page-nav"}
            {if count($NaviFilter->getSearchResults()->getProducts()) > 0}
                {block name='snippets-productlist-page-nav-result-options-sort'}
                    {col cols=12 md="auto" class="displayoptions mb-3 mb-md-0"}
                        {block name='snippets-productlist-page-nav-include-result-options'}
                            {if count($Suchergebnisse->getProducts()) > 0}
                                {opcMountPoint id='opc_before_result_options'}
                            {/if}
                            {if $navid === 'header'}
                                <div id="improve_search">
                                    {include file='productlist/result_options.tpl'}
                                </div>
                            {/if}
                        {/block}
                        {if (!$device->isMobile() || $device->isTablet()) && $navid === 'header'}
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
                            {include file='productlist/layout_options.tpl'}
                        {/if}
                    {/col}
                {/block}
            {/if}
            {block name='snippets-productlist-page-nav-current-page-count'}
                {col cols="auto" class="ml-auto productlist-item-info d-none d-md-flex border-right pr-3"}
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
                                        {link class="page-link" href=$filterPagination->getPrev()->getURL() aria=['label' => {lang key='previous' section='productOverview'}]}<span aria-hidden="true">&#8592;</span>{/link}
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
                                        {link class="page-link" href=$filterPagination->getNext()->getURL() aria=['label' => {lang key='next' section='productOverview'}]}<span aria-hidden="true">&#8594;</span>{/link}
                                    </li>
                                {/block}
                            </ul>
                        </nav>
                    {/col}
                {/block}
            {/if}
        {/row}
        {block name='snippets-productlist-page-nav-hr-bottom'}
            <hr class="mb-5">
        {/block}
    {/if}
{/block}
