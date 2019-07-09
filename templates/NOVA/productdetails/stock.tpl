{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-stock'}
    {assign var=anzeige value=$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige}
    <div class="delivery-status">
    {block name='productdetails-stock-delivery-status'}
        {row class='align-items-center'}
            {if !isset($shippingTime)}
                {block name='productdetails-stock-shipping-time'}
                    {col cols="{if !isset($availability) && $Artikel->cEstimatedDelivery}6{else}12{/if}"}
                        {block name='productdetails-stock-availability'}
                            {if $Artikel->inWarenkorbLegbar === $smarty.const.INWKNICHTLEGBAR_UNVERKAEUFLICH}
                                <span class="status"><small>{lang key='productUnsaleable' section='productDetails'}</small></span>
                            {elseif !$Artikel->nErscheinendesProdukt}
                                {block name='productdetails-stock-include-stock-status'}
                                    {include file='snippets/stock_status.tpl' currentProduct=$Artikel}
                                {/block}
                            {else}
                                {if $anzeige === 'verfuegbarkeit' || $anzeige === 'genau' && $Artikel->fLagerbestand > 0}
                                    <span class="status status-{$Artikel->Lageranzeige->nStatus}">{$Artikel->Lageranzeige->cLagerhinweis[$anzeige]}</span>
                                {elseif $anzeige === 'ampel' && $Artikel->fLagerbestand > 0}
                                    <span class="status status-{$Artikel->Lageranzeige->nStatus}">{$Artikel->Lageranzeige->AmpelText}</span>
                                {/if}
                            {/if}
                        {/block}
                        {* rich snippet availability *}
                        {block name='productdetails-stock-rich-availability'}
                            {if $Artikel->cLagerBeachten === 'N' || $Artikel->fLagerbestand > 0 || $Artikel->cLagerKleinerNull === 'Y'}
                                <link itemprop="availability" href="http://schema.org/InStock" />
                            {elseif $Artikel->nErscheinendesProdukt && $Artikel->Erscheinungsdatum_de !== '00.00.0000' && $Einstellungen.global.global_erscheinende_kaeuflich === 'Y'}
                                <link itemprop="availability" href="http://schema.org/PreOrder" />
                            {elseif $Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerKleinerNull === 'N' && $Artikel->fLagerbestand <= 0}
                                <link itemprop="availability" href="http://schema.org/OutOfStock" />
                            {/if}
                        {/block}
                        {if isset($Artikel->cLieferstatus) && ($Einstellungen.artikeldetails.artikeldetails_lieferstatus_anzeigen === 'Y' ||
                        ($Einstellungen.artikeldetails.artikeldetails_lieferstatus_anzeigen === 'L' && $Artikel->fLagerbestand == 0 && $Artikel->cLagerBeachten === 'Y') ||
                        ($Einstellungen.artikeldetails.artikeldetails_lieferstatus_anzeigen === 'A' && ($Artikel->fLagerbestand > 0 || $Artikel->cLagerKleinerNull === 'Y' || $Artikel->cLagerBeachten !== 'Y')))}
                            {block name='productdetails-stock-delivery-status'}
                                <div class="delivery-status">{lang key='deliveryStatus'}: {$Artikel->cLieferstatus}</div>
                            {/block}
                        {/if}
                    {/col}
                {/block}
            {/if}
            {if !isset($availability)}
            {block name='productdetails-stock-estimated-delivery'}
                {if $Artikel->cEstimatedDelivery}
                    {col}
                        <div class="estimated-delivery">
                            {if !isset($shippingTime)}{lang key='shippingTime'}:{/if}
                            <span class="a{$Artikel->Lageranzeige->nStatus}">{$Artikel->cEstimatedDelivery}</span>
                        </div>
                    {/col}
                {/if}
            {/block}
            {/if}
        {/row}
    {/block}
    </div>
{/block}
