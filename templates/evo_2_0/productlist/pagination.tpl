{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $Suchergebnisse->getPages()->getMaxPage() > 1}
<div class="row list-pageinfo top10">
    <div class="col-xs-6 page-total">
        {lang key='products'} {$Suchergebnisse->getOffsetStart()} - {$Suchergebnisse->getOffsetEnd()} {lang key='of' section='productOverview'} {$Suchergebnisse->getProductCount()}
    </div>
    <div class="col-xs-6 page-current text-right">
        {lang key='page' section='productOverview'}:
        {if $Suchergebnisse->getPages()->getMaxPage() > 1}
            <ul class="pagination pagination-ajax">
                {if $filterPagination->getPrev()->getPageNumber() > 0}
                    <li class="prev">
                        <a href="{$filterPagination->getPrev()->getURL()}"><i class="fa fa-chevron-left"></i></a>
                    </li>
                {/if}

                {foreach $filterPagination->getPages() as $page}
                    <li class="page{if $page->isActive()} active{/if}">
                        <a href="{$page->getURL()}">{$page->getPageNumber()}</a>
                    </li>
                {/foreach}

                {if $filterPagination->getNext()->getPageNumber() > 0}
                    <li class="next">
                        <a href="{$filterPagination->getNext()->getURL()}"><i class="fa fa-chevron-right"></i></a>
                    </li>
                {/if}
            </ul>
        {/if}
    </div>
</div>
{/if}
