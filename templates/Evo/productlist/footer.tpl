{if $Suchergebnisse->Artikel->elemente|@count > 0}
    {if $Einstellungen.navigationsfilter.allgemein_tagfilter_benutzen === 'Y'}
        {if $Suchergebnisse->Tags|@count > 0 && $Suchergebnisse->TagsJSON}
            <hr>
            <div class="panel panel-default tags">
                <div class="panel-heading">{lang key="productsTaggedAs" section="productOverview"}</div>
                <div class="panel-body">
                    {foreach name=tagfilter from=$Suchergebnisse->Tags item=oTag}
                        <a href="{$oTag->cURL}" class="label label-primary tag{$oTag->Klasse}">{$oTag->cName}</a>
                    {/foreach}
                </div>
            </div>
        {/if}
    {/if}

    {if $Einstellungen.navigationsfilter.suchtrefferfilter_nutzen === 'Y'}
        {if $Suchergebnisse->SuchFilter|@count > 0 && $Suchergebnisse->SuchFilterJSON}
            {if !$NaviFilter->hasSearchFilter()}
                <hr>
                <div class="panel panel-default tags">
                    <div class="panel-heading">{lang key="productsSearchTerm" section="productOverview"}</div>
                    <div class="panel-body">
                        {foreach name=suchfilter from=$Suchergebnisse->SuchFilter item=oSuchFilter}
                            <a href="{$oSuchFilter->cURL}" class="label label-primary tag{$oSuchFilter->Klasse}">{$oSuchFilter->cSuche}</a>
                        {/foreach}
                    </div>
                </div>
            {/if}
        {/if}
    {/if}
{/if}

{if $Suchergebnisse->Seitenzahlen->maxSeite > 1 && !empty($oNaviSeite_arr) && $oNaviSeite_arr|@count > 0}
    <div class="row">
        <div class="col-xs-6 col-md-8 col-lg-9">
            <ul class="pagination pagination-ajax">
                {if $Suchergebnisse->Seitenzahlen->AktuelleSeite > 1}
                    <li class="prev">
                        <a href="{$oNaviSeite_arr.zurueck->cURL}">&laquo; {lang key="previous" section="productOverview"}</a>
                    </li>
                {/if}

                {foreach name=seite from=$oNaviSeite_arr item=oNaviSeite}
                    {if !isset($oNaviSeite->nBTN)}
                        <li class="page {if !isset($oNaviSeite->cURL) || $oNaviSeite->cURL|strlen === 0}active{/if}">
                            {if !empty($oNaviSeite->cURL)}
                                <a href="{$oNaviSeite->cURL}">{$oNaviSeite->nSeite}</a>
                            {else}
                                <a href="#" onclick="return false;">{$oNaviSeite->nSeite}</a>
                            {/if}
                        </li>
                    {/if}
                {/foreach}

                {if $Suchergebnisse->Seitenzahlen->AktuelleSeite < $Suchergebnisse->Seitenzahlen->maxSeite}
                    {*
                    <li>
                        .. {lang key="of" section="productOverview"} {$Suchergebnisse->Seitenzahlen->MaxSeiten}
                    </li>
                    *}
                    <li class="next">
                        <a href="{$oNaviSeite_arr.vor->cURL}">{lang key="next" section="productOverview"} &raquo;</a>
                    </li>
                {/if}
            </ul>
        </div>
        <div class="col-xs-6 col-md-4 col-lg-3 text-right">
            <form action="index.php" method="get" class="form-inline pagination">
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
                    <input type="hidden" name="kf" value="{$NaviFilter->getCategoryFilter()->getValue()}" />
                {/if}
                {if $NaviFilter->hasManufacturerFilter()}
                    <input type="hidden" name="hf" value="{$NaviFilter->getManufacturerFilter()->getValue()}" />
                {/if}
                {if $NaviFilter->hasAttributeFilter()}
                    {foreach name=merkmalfilter from=$NaviFilter->getAttributeFilter() item=attributeFilter}
                        <input type="hidden" name="mf{$smarty.foreach.merkmalfilter.iteration}" value="{$attributeFilter->getValue()}" />
                    {/foreach}
                {/if}
                {if $NaviFilter->hasTagFilter()}
                    {foreach name=tagfilter from=$NaviFilter->getTagFilter() item=tagFilter}
                        <input type="hidden" name="tf{$smarty.foreach.tagfilter.iteration}" value="{$tagFilter->getValue()}" />
                    {/foreach}
                {/if}

                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle" type="button" id="pagination-dropdown" data-toggle="dropdown" aria-expanded="true">
                        {lang key="goToPage" section="productOverview"}
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu pagination-ajax" role="menu" aria-labelledby="pagination-dropdown">
                        {foreach name=seite from=$oNaviSeite_arr item=oNaviSeite}
                            {if !isset($oNaviSeite->nBTN)}
                                {if $oNaviSeite->nSeite == $Suchergebnisse->Seitenzahlen->AktuelleSeite}
                                    <li class="active">
                                        <a role="menuitem" class="disabled" href="{$oNaviSeite->cURL}">{$oNaviSeite->nSeite}</a>
                                    </li>
                                {else}
                                    <li>
                                        <a role="menuitem" tabindex="-1" href="{$oNaviSeite->cURL}">{$oNaviSeite->nSeite}</a>
                                    </li>
                                {/if}
                            {/if}
                        {/foreach}
                    </ul>
                </div>
            </form>
        </div>
    </div>
{/if}
