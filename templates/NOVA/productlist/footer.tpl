{block name='productlist-footer'}
    {assign var=Suchergebnisse value=$NaviFilter->getSearchResults(false)}
    {*{if $Suchergebnisse->getProducts()|@count > 0}
        {if $Einstellungen.navigationsfilter.suchtrefferfilter_nutzen === 'Y'
            && $Suchergebnisse->getSearchFilterOptions()|@count > 0
            && $Suchergebnisse->getSearchFilterJSON()
            && !$NaviFilter->hasSearchFilter()}
            {block name='productlist-footer-search-term'}
                <hr>
                {card class="tags search-terms mb-4" subtitle="{lang key='productsSearchTerm' section='productOverview'}"}
                    {foreach $Suchergebnisse->getSearchFilterOptions() as $oSuchFilter}
                        {link href=$oSuchFilter->getURL() class="badge badge-light mr-2 tag{$oSuchFilter->getClass()}"}{$oSuchFilter->getName()}{/link}
                    {/foreach}
                {/card}
            {/block}
        {/if}
    {/if}*}
    {block name='productlist-footer-include-productlist-page-nav'}
        {include file='snippets/productlist_page_nav.tpl' navid='footer' hrTop=true}
    {/block}
{/block}
