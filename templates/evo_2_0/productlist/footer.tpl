{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
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

{include file='./pagination.tpl'}
