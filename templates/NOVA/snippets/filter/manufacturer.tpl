{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-filter-manufacturer'}
    <ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
        {foreach $Suchergebnisse->Herstellerauswahl as $Hersteller}
            {if $Hersteller->nAnzahl >= 1}
                <li>
                    {link rel="nofollow" href=$Hersteller->cURL}
                        <span class="badge badge-light pull-right">{if !isset($nMaxAnzahlArtikel) || !$nMaxAnzahlArtikel}{$Hersteller->nAnzahl}{/if}</span>
                        <span class="value">
                            <i class="fa {if $NaviFilter->hasManufacturerFilter() && $NaviFilter->getManufacturerFilter()->getValue() == $Hersteller->kHersteller}fa-check-square-o{else}fa-square-o{/if} text-muted"></i>
                            {$Hersteller->cName|escape:'html'}
                        </span>
                    {/link}
                </li>
            {/if}
        {/foreach}
    </ul>
{/block}
