{if $Suchergebnisse->getProductCount() > 0}
    {row class="no-gutters productlist-page-nav"}
        {col cols=12 md="auto" class="productlist-item-info"}
            {lang key="products"} {$Suchergebnisse->getOffsetStart()} - {$Suchergebnisse->getOffsetEnd()} {lang key='of' section='productOverview'} {$Suchergebnisse->getProductCount()}
        {/col}
        {if $Suchergebnisse->getPages()->getMaxPage() > 1}
            {col cols=12 md="auto" class="productlist-pagination ml-md-auto"}
                <nav class="navbar-pagination" aria-label="Productlist Navigation">
                    <ul class="pagination">
                        <li class="page-item{if $Suchergebnisse->getPages()->getCurrentPage() == 1} disabled{/if}">
                            {link class="page-link" href=$filterPagination->getPrev()->getURL()}<span aria-hidden="true">&#8592;</span>{/link}
                        </li>
                        <li class="page-item dropdown">
                            <button type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="pagination-site">Seite</span> {$Suchergebnisse->getPages()->getCurrentPage()}
                            </button>
                            <div class="dropdown-menu">
                                {foreach $filterPagination->getPages() as $page}
                                    <div class="dropdown-item page-item{if $page->isActive()} active{/if}">
                                        {link class="page-link" href=$page->getURL()}<span class="pagination-site">Seite</span> {$page->getPageNumber()}{/link}
                                    </div>
                                {/foreach}
                            </div>
                        </li>
                        <li class="page-item{if $Suchergebnisse->getPages()->getCurrentPage() == $Suchergebnisse->getPages()->getMaxPage()} disabled{/if}">
                            {link class="page-link" href=$filterPagination->getNext()->getURL()}<span aria-hidden="true">&#8594;</span>{/link}
                        </li>
                    </ul>
                </nav>
            {/col}

            {*{col cols=6 md=4 lg=3 class="text-right"}
            {form action="{$ShopURL}/" method="get" class="form-inline pagination"}
                {if $NaviFilter->hasCategory()}
                    {input type="hidden" name="k" value="{$NaviFilter->getCategory()->getValue()}"}
                {/if}
                {if $NaviFilter->hasManufacturer()}
                    {input type="hidden" name="h" value="{$NaviFilter->getManufacturer()->getValue()}"}
                {/if}
                {if $NaviFilter->hasSearchQuery()}
                    {input type="hidden" name="l" value="{$NaviFilter->getSearchQuery()->getValue()}"}
                {/if}
                {if $NaviFilter->hasAttributeValue()}
                    {input type="hidden" name="m" value="{$NaviFilter->getAttributeValue()->getValue()}"}
                {/if}
                {if $NaviFilter->hasTag()}
                    {input type="hidden" name="t" value="{$NaviFilter->getTag()->getValue()}"}
                {/if}
                {if $NaviFilter->hasCategoryFilter()}
                    {assign var=cfv value=$NaviFilter->getCategoryFilter()->getValue()}
                    {if is_array($cfv)}
                        {foreach $cfv as $val}
                            {input type="hidden" name="hf" value="{$val}"}
                        {/foreach}
                    {else}
                        {input type="hidden" name="kf" value="{$cfv}"}
                    {/if}
                {/if}
                {if $NaviFilter->hasManufacturerFilter()}
                    {assign var=mfv value=$NaviFilter->getManufacturerFilter()->getValue()}
                    {if is_array($mfv)}
                        {foreach $mfv as $val}
                            {input type="hidden" name="hf" value="{$val}"}
                        {/foreach}
                    {else}
                        {input type="hidden" name="hf" value="{$mfv}"}
                    {/if}
                {/if}
                {if $NaviFilter->hasAttributeFilter()}
                    {foreach $NaviFilter->getAttributeFilter() as $attributeFilter}
                        {input type="hidden" name="mf{$attributeFilter@iteration}" value="{$attributeFilter->getValue()}"}
                    {/foreach}
                {/if}
                {if $NaviFilter->hasTagFilter()}
                    {foreach $NaviFilter->getTagFilter() as $tagFilter}
                        {input type="hidden" name="tf{$tagFilter@iteration}" value="{$tagFilter->getValue()}"}
                    {/foreach}
                {/if}

                {dropdown text="{lang key='goToPage' section='productOverview'}<span class='caret'></span>" id="pagination-dropdown"}
                    {foreach $filterPagination->getPages() as $page}
                        {dropdownitem active=$page->isActive() href="{$page->getURL()}"}
                            {$page->getPageNumber()}
                        {/dropdownitem}
                    {/foreach}
                {/dropdown}
            {/form}
        {/col}*}
        {/if}
    {/row}
{/if}