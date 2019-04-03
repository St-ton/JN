{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-filter-review'}
    <ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
        {foreach $Suchergebnisse->Bewertung as $oBewertung}
            {if $NaviFilter->hasRatingFilter() && $NaviFilter->getRatingFilter()->getValue() == $oBewertung->nStern}
                {block name='snippets-filter-review-rating-equals'}
                    <li>
                        {* @todo: use getter *}
                        {link rel="nofollow" href=$NaviFilter->URL->getRatings() class="active"}
                            <i class="fa fa-check-square-o text-muted"></i>
                            <span class="badge pull-right">{$oBewertung->nAnzahl}</span>
                            <span class="value">
                                {block name='snippets-filter-review-include-rating'}
                                    {include file='productdetails/rating.tpl' stars=$oBewertung->nStern}
                                {/block}
                                {if $NaviFilter->getRatingFilter()->getValue() < 5}
                                    <em>({lang key='from' section='productDetails'} {$oBewertung->nStern}
                                        {if $oBewertung->nStern > 1}
                                            {lang key='starPlural'}
                                        {else}
                                            {lang key='starSingular'}

                                        {/if})
                                    </em>
                                {/if}
                            </span>
                        {/link}
                    </li>
                {/block}
            {elseif $oBewertung->nAnzahl >= 1 && $oBewertung->nStern > 0}
                {block name='snippets-filter-review-rating-greater'}
                    <li>
                        {link rel="nofollow" href=$oBewertung->cURL}
                            <i class="fa fa-square-o text-muted"></i>
                            <span class="badge pull-right">{$oBewertung->nAnzahl}</span>
                            <span class="value">
                                {include file='productdetails/rating.tpl' stars=$oBewertung->nStern}
                                {if $oBewertung->nStern < 5}
                                    <em>
                                        ({lang key='from' section='productDetails'} {$oBewertung->nStern}
                                        {if $oBewertung->nStern > 1}
                                            {lang key='starPlural'}
                                        {else}
                                            {lang key='starSingular'}
                                        {/if})
                                    </em>
                                {/if}
                            </span>
                        {/link}
                    </li>
                {/block}
            {/if}
        {/foreach}
    </ul>
{/block}
