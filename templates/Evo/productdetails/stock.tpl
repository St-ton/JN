{assign var=anzeige value=$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige}
<div class="delivery-status">
{block name='delivery-status'}
    {if !isset($shippingTime)}
        {if $Artikel->inWarenkorbLegbar === $smarty.const.INWKNICHTLEGBAR_UNVERKAEUFLICH}
            <span class="status"><small>{lang key='productUnsaleable' section='productDetails'}</small></span>
        {elseif !$Artikel->nErscheinendesProdukt}
            {include file='snippets/stock_status.tpl' currentProduct=$Artikel}
        {else}
            {if $anzeige === 'verfuegbarkeit' || $anzeige === 'genau' && $Artikel->fLagerbestand > 0}
                <span class="status status-{$Artikel->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$Artikel->Lageranzeige->cLagerhinweis[$anzeige]}</span>
            {elseif $anzeige === 'ampel' && $Artikel->fLagerbestand > 0}
                <span class="status status-{$Artikel->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$Artikel->Lageranzeige->AmpelText}</span>
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
            <p class="delivery-status"><strong>{lang key='deliveryStatus'}</strong>: {$Artikel->cLieferstatus}</p>
        {/if}
    {/if}

    {if !isset($availability)}
        {if $Artikel->cEstimatedDelivery}
            <p class="estimated-delivery"
               data-toggle="popover"
               data-placement="top"
               data-content="{lang key='shippingInformation' section='productDetails' printf=$Firma->country->getName()|cat:':::'|cat:$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL()}">
                {if !isset($availability) && !isset($shippingTime)}<strong>{lang key='shippingTime'}: </strong>{/if}
                <span class="a{$Artikel->Lageranzeige->nStatus} text-nowrap">
                    {$Artikel->cEstimatedDelivery}
                    <span class="fa fa-info-circle"></span>
                </span>
            </p>
        {/if}
    {/if}
{/block}
</div>
