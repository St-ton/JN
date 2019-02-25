{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
    {foreach $Suchergebnisse->Kategorieauswahl as $Kategorie}
        {if $Kategorie->nAnzahl >= 1}
            <li>
                {link rel="nofollow" href=$Kategorie->cURL}
                    <span class="badge pull-right">{if !isset($nMaxAnzahlArtikel) || !$nMaxAnzahlArtikel}{$Kategorie->nAnzahl}{/if}</span>
                    <span class="value">
                        <i class="fa {if $NaviFilter->hasCategoryFilter() && $NaviFilter->getCategory()->getValue() == $Kategorie->kKategorie}fa-check-square-o{else}fa-square-o{/if} text-muted"></i>
                        {$Kategorie->cName|escape:'html'}
                    </span>
                {/link}
            </li>
        {/if}
    {/foreach}
</ul>
