{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{assign var=anzeige value=$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige}
<div class="delivery-status p-3 px-3 mb-3">
{block name='delivery-status'}
    {row}
        {col cols=6}
            {if !$Artikel->nErscheinendesProdukt}
                {if $anzeige !== 'nichts' && $Artikel->cLagerBeachten === 'Y' &&
                ($Artikel->cLagerKleinerNull === 'N' || $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U') &&
                $Artikel->fLagerbestand <= 0 && $Artikel->fZulauf > 0 && isset($Artikel->dZulaufDatum_de) && $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen !== 'N'}
                    {assign var=cZulauf value=$Artikel->fZulauf|cat:':::'|cat:$Artikel->dZulaufDatum_de}
                    <span class="status status-1">{lang key='productInflowing' section='productDetails' printf=$cZulauf}</span>
                {elseif $anzeige !== 'nichts' && $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen !== 'N' && $Artikel->cLagerBeachten === 'Y' &&
                $Artikel->fLagerbestand <= 0 && $Artikel->fLieferantenlagerbestand > 0 && $Artikel->fLieferzeit > 0 &&
                ($Artikel->cLagerKleinerNull === 'N' && $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'I' || $Artikel->cLagerKleinerNull === 'Y' && $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U')}
                    <span class="status status-1">{lang key='supplierStockNotice' printf=$Artikel->fLieferzeit}</span>
                {elseif $anzeige === 'verfuegbarkeit' || $anzeige === 'genau'}
                    <span class="status status-{$Artikel->Lageranzeige->nStatus}">{$Artikel->Lageranzeige->cLagerhinweis[$anzeige]}</span>
                {elseif $anzeige === 'ampel'}
                    <span class="status status-{$Artikel->Lageranzeige->nStatus}">{$Artikel->Lageranzeige->AmpelText}</span>
                {/if}
                {include file='productdetails/warehouse.tpl' tplscope='detail'}
            {else}
                {if $anzeige === 'verfuegbarkeit' || $anzeige === 'genau' && $Artikel->fLagerbestand > 0}
                    <span class="status status-{$Artikel->Lageranzeige->nStatus}">{$Artikel->Lageranzeige->cLagerhinweis[$anzeige]}</span>
                {elseif $anzeige === 'ampel' && $Artikel->fLagerbestand > 0}
                    <span class="status status-{$Artikel->Lageranzeige->nStatus}">{$Artikel->Lageranzeige->AmpelText}</span>
                {/if}
            {/if}

            {* rich snippet availability *}
            {if $Artikel->cLagerBeachten === 'N' || $Artikel->fLagerbestand > 0 || $Artikel->cLagerKleinerNull === 'Y'}
                <link itemprop="availability" href="http://schema.org/InStock" />
            {elseif $Artikel->nErscheinendesProdukt && $Artikel->Erscheinungsdatum_de !== '00.00.0000' && $Einstellungen.global.global_erscheinende_kaeuflich === 'Y'}
                <link itemprop="availability" href="http://schema.org/PreOrder" />
            {elseif $Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerKleinerNull === 'N' && $Artikel->fLagerbestand <= 0}
                <link itemprop="availability" href="http://schema.org/OutOfStock" />
            {/if}

            {if isset($Artikel->cLieferstatus) && ($Einstellungen.artikeldetails.artikeldetails_lieferstatus_anzeigen === 'Y' ||
            ($Einstellungen.artikeldetails.artikeldetails_lieferstatus_anzeigen === 'L' && $Artikel->fLagerbestand == 0 && $Artikel->cLagerBeachten === 'Y') ||
            ($Einstellungen.artikeldetails.artikeldetails_lieferstatus_anzeigen === 'A' && ($Artikel->fLagerbestand > 0 || $Artikel->cLagerKleinerNull === 'Y' || $Artikel->cLagerBeachten !== 'Y')))}
                <div class="delivery-status">{lang key='deliveryStatus'}: {$Artikel->cLieferstatus}</div>
            {/if}
        {/col}
        {col cols=6}
            {if $Artikel->cEstimatedDelivery}
                <div class="estimated-delivery">
                    {lang key='shippingTime'}: <span class="a{$Artikel->Lageranzeige->nStatus}">{$Artikel->cEstimatedDelivery}</span>
                </div>
            {/if}
        {/col}
    {/row}
{/block}
</div>
