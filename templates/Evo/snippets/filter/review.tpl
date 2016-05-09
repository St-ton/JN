<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
    {foreach name=bewertungen from=$Suchergebnisse->Bewertung item=oBewertung}
        {if isset($NaviFilter->BewertungFilter) && $NaviFilter->BewertungFilter->nSterne == $oBewertung->nStern}
            <li><a rel="nofollow" href="{$NaviFilter->URL->cAlleBewertungen}" class="active"><i class="fa fa-check-square-o text-muted"></i> {include file="productdetails/rating.tpl" stars=$oBewertung->nStern} {if $NaviFilter->BewertungFilter->nSterne < 5}<em>({lang key="from" section="productDetails"} {$oBewertung->nStern} {if $oBewertung->nStern > 1}{lang key="starPlural"}{else}{lang key="starSingular"}{/if})</em>{/if} <span class="badge">{$oBewertung->nAnzahl}</span></a></li>
        {elseif $Suchergebnisse->GesamtanzahlArtikel > $oBewertung->nAnzahl}{* only show filters that reduce number of search results *}
            {if $oBewertung->nAnzahl >= 1 && $oBewertung->nStern > 0}
                <li><a rel="nofollow" href="{$oBewertung->cURL}"><i class="fa fa-square-o text-muted"></i> {include file="productdetails/rating.tpl" stars=$oBewertung->nStern} {if $oBewertung->nStern < 5}<em>({lang key="from" section="productDetails"} {$oBewertung->nStern} {if $oBewertung->nStern > 1}{lang key="starPlural"}{else}{lang key="starSingular"}{/if})</em>{/if} <span class="badge">{$oBewertung->nAnzahl}</span></a></li>
            {/if}
        {/if}
    {/foreach}
</ul>