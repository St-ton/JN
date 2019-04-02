{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{assign var=Suchergebnisse value=$NaviFilter->getSearchResults(false)}
{if $Suchergebnisse->getProducts()|@count > 0}
    {if $Einstellungen.navigationsfilter.allgemein_tagfilter_benutzen !== 'N'
        && $Einstellungen.navigationsfilter.allgemein_tagfilter_benutzen !== 'box'
        && $Suchergebnisse->getTagFilterOptions()|@count > 0 && $Suchergebnisse->getTagFilterJSON()}
        {card class="tags mb-4" subtitle="{lang key='productsTaggedAs' section='productOverview'}"}
            {foreach $Suchergebnisse->getTagFilterOptions() as $oTag}
                {link href=$oTag->getURL() class="badge badge-light mr-2 tag{$oTag->getClass()}"}{$oTag->getName()}{/link}
            {/foreach}
        {/card}
    {/if}
    {if $Einstellungen.navigationsfilter.suchtrefferfilter_nutzen === 'Y'
        && $Suchergebnisse->getSearchFilterOptions()|@count > 0
        && $Suchergebnisse->getSearchFilterJSON()
        && !$NaviFilter->hasSearchFilter()}
        <hr>
        {card class="tags search-terms mb-4" subtitle="{lang key='productsSearchTerm' section='productOverview'}"}
            {foreach $Suchergebnisse->getSearchFilterOptions() as $oSuchFilter}
                {link href=$oSuchFilter->getURL() class="badge badge-light mr-2 tag{$oSuchFilter->getClass()}"}{$oSuchFilter->getName()}{/link}
            {/foreach}
        {/card}
    {/if}
{/if}

{include file='snippets/productlist_page_nav.tpl'}
