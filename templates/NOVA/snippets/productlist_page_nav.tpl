{if $Suchergebnisse->getProductCount() > 0}
    {row class="list-pageinfo"}
    {col cols=4 class="page-total pt-2 pl-0"}
    {lang key='products'} {$Suchergebnisse->getOffsetStart()} - {$Suchergebnisse->getOffsetEnd()} {lang key='of' section='productOverview'} {$Suchergebnisse->getProductCount()}
    {/col}
    {if $Suchergebnisse->getPages()->getMaxPage() > 1}
        {col cols=8 class="text-right pr-0"}
            <span class="mr-2 d-inline-block">{lang key='page' section='productOverview'}:</span>
        {buttongroup class="pagination pagination-ajax d-inline-block"}
        {if $filterPagination->getPrev()->getPageNumber() > 0}
            {link class="prev btn btn-link" href="{$filterPagination->getPrev()->getURL()}"}<i class="fas fa-chevron-left"></i>{/link}
        {/if}

        {foreach $filterPagination->getPages() as $page}
            {link disabled=$page->isActive() href="{$page->getURL()}" disabled=$page->isActive()
            class="page{if $page->isActive()} active{/if} btn btn-link"}
            {$page->getPageNumber()}
            {/link}
        {/foreach}

        {if $filterPagination->getNext()->getPageNumber() > 0}
            {link class="next btn btn-link" href="{$filterPagination->getNext()->getURL()}"}<i class="fas fa-chevron-right"></i>{/link}
        {/if}
        {/buttongroup}
        {/col}
        {*{col cols=6 md=4 lg=3 class="text-right"}
            {form action="{$ShopURL}/" method="get" class="form-inline pagination"}
                {$jtl_token}
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