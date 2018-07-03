{assign var=Suchergebnisse value=$NaviFilter->getSearchResults(false)}
{if $Suchergebnisse->getProducts()|@count > 0}
    {if $Einstellungen.navigationsfilter.allgemein_tagfilter_benutzen !== 'N'
        && $Einstellungen.navigationsfilter.allgemein_tagfilter_benutzen !== 'box'
        && $Suchergebnisse->getTagFilterOptions()|@count > 0 && $Suchergebnisse->getTagFilterJSON()}
        <hr>
        <div class="panel panel-default tags">
            <div class="panel-heading">{lang key='productsTaggedAs' section='productOverview'}</div>
            <div class="panel-body">
                {foreach $Suchergebnisse->getTagFilterOptions() as $oTag}
                    <a href="{$oTag->getURL()}" class="label label-primary tag{$oTag->getClass()}">{$oTag->getName()}</a>
                {/foreach}
            </div>
        </div>
    {/if}
    {if $Einstellungen.navigationsfilter.suchtrefferfilter_nutzen === 'Y'
        && $Suchergebnisse->getSearchFilterOptions()|@count > 0
        && $Suchergebnisse->getSearchFilterJSON()
        && !$NaviFilter->hasSearchFilter()}
        <hr>
        <div class="panel panel-default tags search-terms">
            <div class="panel-heading">{lang key='productsSearchTerm' section='productOverview'}</div>
            <div class="panel-body">
                {foreach $Suchergebnisse->getSearchFilterOptions() as $oSuchFilter}
                    <a href="{$oSuchFilter->getURL()}" class="label label-primary tag{$oSuchFilter->getClass()}">{$oSuchFilter->getName()}</a>
                {/foreach}
            </div>
        </div>
    {/if}
{/if}

{if $Suchergebnisse->getPages()->getMaxPage() > 1}
    <div class="row">
        <div class="col-xs-6 col-md-8 col-lg-9">
            <ul class="pagination pagination-ajax">
                {if $filterPagination->getPrev()->getPageNumber() > 0}
                    <li class="prev">
                        <a href="{$filterPagination->getPrev()->getURL()}">&laquo; {lang key='previous' section='productOverview'}</a>
                    </li>
                {/if}

                {foreach $filterPagination->getPages() as $page}
                    <li class="page{if $page->isActive()} active{/if}">
                        <a href="{$page->getURL()}">{$page->getPageNumber()}</a>
                    </li>
                {/foreach}

                {if $filterPagination->getNext()->getPageNumber() > 0}
                    <li class="next">
                        <a href="{$filterPagination->getNext()->getURL()}">{lang key='next' section='productOverview'} &raquo;</a>
                    </li>
                {/if}
            </ul>
        </div>
        <div class="col-xs-6 col-md-4 col-lg-3 text-right">
            <form action="{$ShopURL}/" method="get" class="form-inline pagination">
                {$jtl_token}
                {if $NaviFilter->hasCategory()}
                    <input type="hidden" name="k" value="{$NaviFilter->getCategory()->getValue()}" />
                {/if}
                {if $NaviFilter->hasManufacturer()}
                    <input type="hidden" name="h" value="{$NaviFilter->getManufacturer()->getValue()}" />
                {/if}
                {if $NaviFilter->hasSearchQuery()}
                    <input type="hidden" name="l" value="{$NaviFilter->getSearchQuery()->getValue()}" />
                {/if}
                {if $NaviFilter->hasAttributeValue()}
                    <input type="hidden" name="m" value="{$NaviFilter->getAttributeValue()->getValue()}" />
                {/if}
                {if $NaviFilter->hasTag()}
                    <input type="hidden" name="t" value="{$NaviFilter->getTag()->getValue()}" />
                {/if}
                {if $NaviFilter->hasCategoryFilter()}
                    {assign var=cfv value=$NaviFilter->getCategoryFilter()->getValue()}
                    {if is_array($cfv)}
                        {foreach $cfv as $val}
                            <input type="hidden" name="hf" value="{$val}" />
                        {/foreach}
                    {else}
                        <input type="hidden" name="kf" value="{$cfv}" />
                    {/if}
                {/if}
                {if $NaviFilter->hasManufacturerFilter()}
                    {assign var=mfv value=$NaviFilter->getManufacturerFilter()->getValue()}
                    {if is_array($mfv)}
                        {foreach $mfv as $val}
                            <input type="hidden" name="hf" value="{$val}" />
                        {/foreach}
                    {else}
                        <input type="hidden" name="hf" value="{$mfv}" />
                    {/if}
                {/if}
                {if $NaviFilter->hasAttributeFilter()}
                    {foreach $NaviFilter->getAttributeFilter() as $attributeFilter}
                        <input type="hidden" name="mf{$attributeFilter@iteration}" value="{$attributeFilter->getValue()}" />
                    {/foreach}
                {/if}
                {if $NaviFilter->hasTagFilter()}
                    {foreach $NaviFilter->getTagFilter() as $tagFilter}
                        <input type="hidden" name="tf{$tagFilter@iteration}" value="{$tagFilter->getValue()}" />
                    {/foreach}
                {/if}

                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle" type="button" id="pagination-dropdown" data-toggle="dropdown" aria-expanded="true">
                        {lang key='goToPage' section='productOverview'}
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu pagination-ajax" role="menu" aria-labelledby="pagination-dropdown">
                        {foreach $filterPagination->getPages() as $page}
                            {if $page->isActive()}
                                <li class="active">
                                    <a role="menuitem" class="disabled" href="{$page->getURL()}">{$page->getPageNumber()}</a>
                                </li>
                            {else}
                                <li>
                                    <a role="menuitem" tabindex="-1" href="{$page->getURL()}">{$page->getPageNumber()}</a>
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                </div>
            </form>
        </div>
    </div>
{/if}
