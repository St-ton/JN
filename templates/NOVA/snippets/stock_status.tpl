{block name='snippets-stock-status'}
    {assign var=anzeige value=$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige}
    {if $anzeige !== 'nichts'
        && $currentProduct->cLagerBeachten === 'Y'
        && ($currentProduct->cLagerKleinerNull === 'N'
            || $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U')
        && $currentProduct->fLagerbestand <= 0
        && $currentProduct->fZulauf > 0
        && isset($currentProduct->dZulaufDatum_de)
        && $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen !== 'N'}
        {assign var=cZulauf value=$currentProduct->fZulauf|cat:':::'|cat:$currentProduct->dZulaufDatum_de}
        {block name='snippets-stock-status-in-flowing'}
            <span class="status status-1">{lang key='productInflowing' section='productDetails' printf=$cZulauf}</span>
        {/block}
    {elseif $anzeige !== 'nichts'
        && $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen !== 'N'
        && $currentProduct->cLagerBeachten === 'Y'
        && $currentProduct->fLagerbestand <= 0
        && $currentProduct->fLieferantenlagerbestand > 0
        && $currentProduct->fLieferzeit > 0
        && ($currentProduct->cLagerKleinerNull === 'N'
            && $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'I'
            || $currentProduct->cLagerKleinerNull === 'Y'
            && $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U')}
        {block name='snippets-stock-status-supllier-stock-notice'}
            <span class="status status-1">{lang key='supplierStockNotice' printf=$currentProduct->fLieferzeit}</span>
        {/block}
    {elseif $anzeige === 'verfuegbarkeit'
        || $anzeige === 'genau'}
        {block name='snippets-stock-status-exact'}
            <span class="status status-{$currentProduct->Lageranzeige->nStatus}">
                <span class="fas fa-truck mr-2"></span>{$currentProduct->Lageranzeige->cLagerhinweis[$anzeige]}
            </span>
        {/block}
    {elseif $anzeige === 'ampel'}
        {block name='snippets-stock-status-traffic-light'}
            <span class="status status-{$currentProduct->Lageranzeige->nStatus}">
                <span class="fas fa-truck mr-2"></span>{$currentProduct->Lageranzeige->AmpelText}
            </span>
        {/block}
    {/if}
{/block}
