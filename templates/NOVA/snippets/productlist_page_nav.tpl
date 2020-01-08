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
                            {block name='snippets-productlist-page-nav-actions'}
                                {block name='snippets-productlist-page-nav-actions-sort'}
                                    {dropdown class="filter-type-FilterItemSort btn-group" variant="outline-secondary" text="{lang key='sorting' section='productOverview'}"}
                                        {foreach $Suchergebnisse->getSortingOptions() as $option}
                                            {dropdownitem rel="nofollow" href=$option->getURL() class="filter-item" active=$option->isActive()}
                                                {$option->getName()}
                                            {/dropdownitem}
                                        {/foreach}
                                    {/dropdown}
                                {/block}
                                {block name='snippets-productlist-page-nav-actions-items'}
                                    {dropdown class="filter-type-FilterItemLimits btn-group ml-2" variant="outline-secondary" text="{lang key='productsPerPage' section='productOverview'}"}
                                        {foreach $Suchergebnisse->getLimitOptions() as $option}
                                            {dropdownitem rel="nofollow" href=$option->getURL() class="filter-item" active=$option->isActive()}
                                                {$option->getName()}
                                            {/dropdownitem}
                                        {/foreach}
                                    {/dropdown}
                                {/block}
                                {if !$device->isMobile()}
                                    {block name='snippets-productlist-page-nav-include-layout-options'}
                                        {include file='productlist/layout_options.tpl'}
                                    {/block}
                                {/if}
                            {/block}
                        {/if}
                    {/col}
                {/block}
            {/if}
            {block name='snippets-productlist-page-nav-current-page-count'}
                {col cols="auto" class="ml-md-auto mb-2 mb-md-0 mx-auto mx-md-0 productlist-item-info d-flex {if $Suchergebnisse->getPages()->getMaxPage() > 1}border-md-right{/if} pr-md-3"}
                    {lang key="products"} {$Suchergebnisse->getOffsetStart()} - {$Suchergebnisse->getOffsetEnd()} {lang key='of' section='productOverview'} {$Suchergebnisse->getProductCount()}
                {/col}
            {/block}
            {if $Suchergebnisse->getPages()->getMaxPage() > 1 && !($device->isMobile() && $navid === 'header')}
                {block name='snippets-productlist-page-nav-page-nav'}
                    {col cols=12 md="auto" class="productlist-pagination"}
                        <nav class="navbar-pagination" aria-label="Productlist Navigation">
                            <ul class="pagination">
                                {block name='snippets-productlist-page-nav-first-page'}
                                    <li class="page-item{if $Suchergebnisse->getPages()->getCurrentPage() == 1} disabled{/if}">
                                        {link class="page-link" href=$filterPagination->getPrev()->getURL() aria=['label' => {lang key='previous' section='productOverview'}]}<i class="fas fa-long-arrow-alt-left"></i>{/link}
                                    </li>
                                {/block}
                                <li class="page-item dropdown">
                                    {block name='snippets-productlist-page-nav-button'}
                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="pagination-site">{lang key='page'}</span> {$Suchergebnisse->getPages()->getCurrentPage()}
                                        </button>
                                    {/block}
                                    <div class="dropdown-menu shadow-none">
                                        {block name='snippets-productlist-page-nav-pages'}
                                            {foreach $filterPagination->getPages() as $page}
                                                <div class="dropdown-item page-item{if $page->isActive()} active{/if}">
                                                    {link class="page-link" href=$page->getURL()}<span class="pagination-site">{lang key='page'}</span> {$page->getPageNumber()}{/link}
                                                </div>
                                            {/foreach}
                                        {/block}
                                    </div>
                                </li>
                                {block name='snippets-productlist-page-nav-last-page'}
                                    <li class="page-item{if $Suchergebnisse->getPages()->getCurrentPage() == $Suchergebnisse->getPages()->getMaxPage()} disabled{/if}">
                                        {link class="page-link" href=$filterPagination->getNext()->getURL() aria=['label' => {lang key='next' section='productOverview'}]}<i class="fas fa-long-arrow-alt-right"></i>{/link}
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
