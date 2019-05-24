{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{* nur anzeigen, wenn >1 Warenlager aktiv und Artikel ist auf Lager/im Zulauf/Ueberverkaeufe erlaubt/beachtet kein Lager *}
{block name='productdetails-warehouse'}
    {assign var=anzeige value=$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige}
    {if $anzeige !== 'nichts'
        && isset($Artikel->oWarenlager_arr)
        && $Artikel->oWarenlager_arr|@count > 1
        && ($Artikel->cLagerBeachten !== 'Y'
            || $Artikel->cLagerKleinerNull === 'Y'
            || $Artikel->fLagerbestand > 0
            || $Artikel->fZulauf > 0)}
        {if $tplscope === 'detail'}
            {block name='productdetails-warehouse-detail-link'}
                {link data=["toggle"=>"popover",
                    "placement"=>"left",
                    "container"=>"body",
                    "selector"=>"#popover-warehouse",
                    "trigger"=>"click hover"]
                }
                    <i class="fas fa-map-marker-alt" title="{lang key='availability' section='productDetails'}"></i>
                {/link}
            {/block}
        {/if}
        {block name='productdetails-warehouse-warehouses'}
            <div class="d-none" id="popover-warehouse">
                <table class="table table-striped warehouse-table">
                {foreach $Artikel->oWarenlager_arr as $oWarenlager}
                    <tr>
                        <td class="name"><strong>{$oWarenlager->getName()}</strong></td>
                        <td class="delivery-status">
                        {if $anzeige !== 'nichts'
                            && $Artikel->cLagerBeachten === 'Y'
                            && ($Artikel->cLagerKleinerNull === 'N'
                                || $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U')
                            && $oWarenlager->getStock() <= 0
                            && $oWarenlager->getBackorder() > 0
                            && $oWarenlager->getBackorderDate() !== null}
                            {assign var=cZulauf value=$oWarenlager->getBackorder()|cat:':::'|cat:$oWarenlager->getBackorderDateDE()}
                            <span class="signal_image status-1"><span>{lang key='productInflowing' section='productDetails' printf=$cZulauf}</span></span>
                        {elseif $anzeige === 'verfuegbarkeit' || $anzeige === 'genau'}
                            <span class="signal_image status-{$oWarenlager->oLageranzeige->nStatus}">{$oWarenlager->oLageranzeige->cLagerhinweis[$anzeige]}</span>
                        {elseif $anzeige === 'ampel'}
                            <span><span class="signal_image status-{$oWarenlager->oLageranzeige->nStatus}">{$oWarenlager->oLageranzeige->AmpelText}</span></span>
                        {/if}
                        </td>
                    </tr>
                {/foreach}
                </table>
            </div>
        {/block}
    {/if}
{/block}
