<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
    {if $NaviFilter->hasPriceRangeFilter()}
        {if $NaviFilter->getPriceRangeFilter()->getOffsetStart() >= 0 && $NaviFilter->getPriceRangeFilter()->getOffsetEnd() > 0}
            <li>
                {*@todo: use getter*}
                <a href="{$NaviFilter->URL->getPriceRanges()}" rel="nofollow" class="active">
                    <span class="value">
                        <i class="fa fa-check-square-o text-muted"></i> {$NaviFilter->getPriceRangeFilter()->getOffsetStartLocalized()} - {$NaviFilter->getPriceRangeFilter()->getOffsetEndLocalized()}
                    </span>
                </a>
            </li>
        {/if}
    {else}
        {foreach $Suchergebnisse->Preisspanne as $oPreisspannenfilter}
            <li>
                <a href="{$oPreisspannenfilter->cURL}" rel="nofollow">
                    <span class="badge pull-right">{$oPreisspannenfilter->nAnzahlArtikel}</span>
                    <span class="value">
                        <i class="fa fa-square-o text-muted"></i> {$oPreisspannenfilter->getOffsetStartLocalized()} - {$oPreisspannenfilter->getOffsetEndLocalized()}
                    </span>
                </a>
            </li>
        {/foreach}
    {/if}
</ul>