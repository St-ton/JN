{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-productlist-page-nav'}
    {if $Suchergebnisse->getProductCount() > 0}
        {include file='snippets/opc_mount_point.tpl' id='opc_before_page_nav_'|cat:$navid}
        {row class="no-gutters productlist-page-nav"}
            {block name='snippets-productlist-page-nav-current-page-count'}
                {col cols=12 md="auto" class="productlist-item-info"}
                    {lang key="products"} {$Suchergebnisse->getOffsetStart()} - {$Suchergebnisse->getOffsetEnd()} {lang key='of' section='productOverview'} {$Suchergebnisse->getProductCount()}
                {/col}
            {/block}
            {if $Suchergebnisse->getPages()->getMaxPage() > 1}
                {block name='snippets-productlist-page-nav-page-nav'}
                    {col cols=12 md="auto" class="productlist-pagination ml-md-auto"}
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
