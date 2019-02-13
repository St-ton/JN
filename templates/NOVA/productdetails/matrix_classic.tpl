{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{capture name='outofstock' assign='outofstockInfo'}<span class="delivery-status"><small class="status-0">{lang key='soldout'}</small></span>{/capture}
<div class="table-responsive">
    <table class="table table-striped table-hover variation-matrix">
        {* ****** 2-dimensional ****** *}
        {if $Artikel->VariationenOhneFreifeld|@count == 2}
            <thead>
            <tr>
                <td>&nbsp;</td>
                {foreach $Artikel->VariationenOhneFreifeld[0]->Werte as $oVariationWertHead}
                    <td>
                        {if $Artikel->oVariBoxMatrixBild_arr|@count > 0
                            && (($Artikel->nIstVater == 1
                            && $Artikel->oVariBoxMatrixBild_arr[0]->nRichtung == 0)
                            || $Artikel->nIstVater == 0)}
                            {foreach $Artikel->oVariBoxMatrixBild_arr as $oVariBoxMatrixBild}
                                {if $oVariBoxMatrixBild->kEigenschaftWert == $oVariationWertHead->kEigenschaftWert}
                                    {image src="{$oVariBoxMatrixBild->cBild}" fluid=true alt=""}<br>
                                {/if}
                            {/foreach}
                        {/if}
                        <strong>{$oVariationWertHead->cName}</strong>
                    </td>
                {/foreach}
            </tr>
            </thead>
            <tbody>
            {assign var=pushed value=0}
            {if isset($Artikel->VariationenOhneFreifeld[1]->Werte)}
                {foreach $Artikel->VariationenOhneFreifeld[1]->Werte as $oVariationWert1}
                    {assign var=kEigenschaftWert1 value=$oVariationWert1->kEigenschaftWert}
                    <tr>
                        <td>
                            {if $Artikel->oVariBoxMatrixBild_arr|@count > 0
                                && (($Artikel->nIstVater == 1
                                        && $Artikel->oVariBoxMatrixBild_arr[0]->nRichtung == 1)
                                    || $Artikel->nIstVater == 0)}
                                {foreach $Artikel->oVariBoxMatrixBild_arr as $oVariBoxMatrixBild}
                                    {if $oVariBoxMatrixBild->kEigenschaftWert == $oVariationWert1->kEigenschaftWert}
                                        {image src="{$oVariBoxMatrixBild->cBild}" fluid=true alt=""}<br>
                                    {/if}
                                {/foreach}
                            {/if}
                            <strong>{$oVariationWert1->cName}</strong>
                        </td>
                        {foreach $Artikel->VariationenOhneFreifeld[0]->Werte as $oVariationWert0}
                            {assign var=bAusblenden value=false}
                            {if $Artikel->nVariationKombiNichtMoeglich_arr|@count > 0}
                                {foreach $Artikel->nVariationKombiNichtMoeglich_arr[$kEigenschaftWert1] as $kEigenschaftWertNichtMoeglich}
                                    {if $kEigenschaftWertNichtMoeglich == $oVariationWert0->kEigenschaftWert
                                        && $Einstellungen.artikeldetails.artikeldetails_warenkorbmatrix_lagerbeachten !== 'N'}
                                        {assign var=bAusblenden value=true}
                                    {/if}
                                {/foreach}
                            {/if}
                            {if !$bAusblenden}
                                {assign var=cVariBox value=$oVariationWert0->kEigenschaft|cat:':'|cat:$oVariationWert0->kEigenschaftWert|cat:'_'|cat:$oVariationWert1->kEigenschaft|cat:':'|cat:$oVariationWert1->kEigenschaftWert}
                                {if isset($Artikel->oVariationKombiKinderAssoc_arr[$cVariBox])}
                                    {assign var=child value=$Artikel->oVariationKombiKinderAssoc_arr[$cVariBox]}
                                {/if}
                                <td>
                                    {if $Einstellungen.global.global_erscheinende_kaeuflich === 'N'
                                        && isset($child->nErscheinendesProdukt) && $child->nErscheinendesProdukt == 1}
                                        <small>
                                            {lang key='productAvailableFrom'}: <strong>{$child->Erscheinungsdatum_de}</strong>
                                        </small>
                                    {elseif isset($child->nNichtLieferbar) && $child->nNichtLieferbar == 1}
                                        {if isset($child->nErscheinendesProdukt) && $child->nErscheinendesProdukt == 1}
                                            <small>
                                                {lang key='productAvailableFrom'}: <strong>{$child->Erscheinungsdatum_de}</strong>
                                            </small>
                                        {else}
                                            {$outofstockInfo}
                                        {/if}
                                    {elseif (isset($child->bHasKonfig)
                                        && $child->bHasKonfig == true)
                                        || (isset($child->nVariationAnzahl)
                                            && isset($child->nVariationOhneFreifeldAnzahl)
                                            && $child->nVariationAnzahl > $child->nVariationOhneFreifeldAnzahl)}
                                        <div>
                                            {link href="{$child->cSeo}" title="{lang key='configure'} {$oVariationWert0->cName}-{$oVariationWert1->cName}" class="btn btn-primary configurepos"}
                                                <i class="fa fa-cogs"></i>
                                                <span class="d-none d-sm-inline-block pl-2">{lang key='configure'}</span>
                                            {/link}
                                        </div>
                                        {if isset($child->nErscheinendesProdukt) && $child->nErscheinendesProdukt == 1}
                                            <div>
                                                <small>
                                                    {lang key='productAvailableFrom'}: <strong>{$child->Erscheinungsdatum_de}</strong>
                                                </small>
                                            </div>
                                        {/if}
                                        <div class="delivery-status">
                                            <small>
                                                {if !isset($child->nErscheinendesProdukt) || !$child->nErscheinendesProdukt}
                                                    {if $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige !== 'nichts'
                                                        && (isset($child->cLagerBeachten) && $child->cLagerBeachten === 'Y')
                                                        && ($child->cLagerKleinerNull === 'N'
                                                            || $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U')
                                                        && $child->fLagerbestand <= 0
                                                        && $child->fZulauf > 0
                                                        && isset($child->dZulaufDatum_de)}
                                                        {assign var=cZulauf value=$child->fZulauf|cat:':::'|cat:$child->dZulaufDatum_de}
                                                        <span class="status status-1"><i class="fa fa-truck"></i> {lang key='productInflowing' section='productDetails' printf=$cZulauf}</span>
                                                    {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige !== 'nichts'
                                                        && $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen !== 'N'
                                                        && $child->cLagerBeachten === 'Y'
                                                        && $child->fLagerbestand <= 0
                                                        && $child->fLieferantenlagerbestand > 0
                                                        && $child->fLieferzeit > 0
                                                        && ($child->cLagerKleinerNull === 'N'
                                                            || $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U')}
                                                        <span class="status status-1"><i class="fa fa-truck"></i> {lang key='supplierStockNotice' printf=$child->fLieferzeit}</span>
                                                    {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'verfuegbarkeit'
                                                        || $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'genau'}
                                                        <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->cLagerhinweis[$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige]}</span>
                                                    {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'ampel'}
                                                        <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->AmpelText}</span>
                                                    {/if}
                                                    {include file='productdetails/warehouse.tpl' tplscope='detail'}
                                                {else}
                                                    {if $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'verfuegbarkeit'
                                                        || $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'genau'
                                                        && ((isset($child->fLagerbestand) && $child->fLagerbestand > 0)
                                                            || (isset($child->cLagerKleinerNull) && $child->cLagerKleinerNull === 'Y'))}
                                                        <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->cLagerhinweis[$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige]}</span>
                                                    {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'ampel'
                                                        && ((isset($child->fLagerbestand) && $child->fLagerbestand > 0)
                                                            || (isset($child->cLagerKleinerNull) && $child->cLagerKleinerNull === 'Y'))}
                                                        <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->AmpelText}</span>
                                                    {/if}
                                                {/if}
                                            </small>
                                        </div>
                                    {else}
                                        {inputgroup class="{if isset($smarty.session.variBoxAnzahl_arr[$cVariBox]->bError)}has-error{/if}"}
                                            {input placeholder="0"
                                                name="variBoxAnzahl[{$oVariationWert1->kEigenschaft}:{$oVariationWert1->kEigenschaftWert}_{$oVariationWert0->kEigenschaft}:{$oVariationWert0->kEigenschaftWert}]"
                                                type="text"
                                                aria=["label"=>"{lang key='quantity'} {$oVariationWert0->cName}-{$oVariationWert1->cName}"]
                                                value="{if isset($smarty.session.variBoxAnzahl_arr[$cVariBox]->fAnzahl)}{$smarty.session.variBoxAnzahl_arr[$cVariBox]->fAnzahl|replace_delim}{/if}"
                                                class="text-right"}
                                            {if $Artikel->nIstVater == 1}
                                                {if isset($child->Preise->cVKLocalized[$NettoPreise]) && $child->Preise->cVKLocalized[$NettoPreise] > 0}
                                                    {inputgroupaddon append=true}
                                                        {inputgrouptext}
                                                            &times; {$child->Preise->cVKLocalized[$NettoPreise]}{if !empty($child->Preise->cPreisVPEWertInklAufpreis[$NettoPreise])} <small>({$child->Preise->cPreisVPEWertInklAufpreis[$NettoPreise]})</small>{/if}
                                                        {/inputgrouptext}
                                                    {/inputgroupaddon}
                                                {elseif isset($child->Preise->cVKLocalized[$NettoPreise])
                                                    && $child->Preise->cVKLocalized[$NettoPreise]}
                                                    {assign var=cVariBox value=$oVariationWert1->kEigenschaft|cat:':'|cat:$oVariationWert1->kEigenschaftWert|cat:'_'|cat:$oVariationWert0->kEigenschaft|cat:':'|cat:$oVariationWert0->kEigenschaftWert}
                                                    {inputgroupaddon append=true}
                                                        {inputgrouptext}
                                                            &times; {$child->Preise->cVKLocalized[$NettoPreise]}{if !empty($child->Preise->cPreisVPEWertInklAufpreis[$NettoPreise])} <small>({$child->Preise->cPreisVPEWertInklAufpreis[$NettoPreise]})</small>{/if}
                                                        {/inputgrouptext}
                                                    {/inputgroupaddon}
                                                {/if}
                                            {elseif $Einstellungen.artikeldetails.artikel_variationspreisanzeige == 1
                                                && ($oVariationWert0->fAufpreisNetto != 0 || $oVariationWert1->fAufpreisNetto != 0)}
                                                {if !isset($oVariationWert1->fAufpreis[1])}
                                                    {assign var=ovw1 value=0}
                                                {else}
                                                    {assign var=ovw1 value=$oVariationWert1->fAufpreis[1]}
                                                {/if}
                                                {if !isset($oVariationWert0->fAufpreis[1])}
                                                    {assign var=ovw0 value=0}
                                                {else}
                                                    {assign var=ovw0 value=$oVariationWert0->fAufpreis[1]}
                                                {/if}

                                                {math equation='x+y' x=$ovw0 y=$ovw1 assign='fAufpreis'}
                                                {inputgroupaddon append=true}
                                                    {inputgrouptext}
                                                        {gibPreisStringLocalizedSmarty bAufpreise=true fAufpreisNetto=$fAufpreis fVKNetto=$Artikel->Preise->fVKNetto kSteuerklasse=$Artikel->kSteuerklasse nNettoPreise=$NettoPreise fVPEWert=$Artikel->fVPEWert cVPEEinheit=$Artikel->cVPEEinheit FunktionsAttribute=$Artikel->FunktionsAttribute}
                                                    {/inputgrouptext}
                                                {/inputgroupaddon}
                                            {elseif $Einstellungen.artikeldetails.artikel_variationspreisanzeige == 2
                                                && ($oVariationWert0->fAufpreisNetto != 0 || $oVariationWert1->fAufpreisNetto != 0)}
                                                {if !isset($oVariationWert1->fAufpreis[1])}
                                                    {assign var=ovw1 value=0}
                                                {else}
                                                    {assign var=ovw1 value=$oVariationWert1->fAufpreis[1]}
                                                {/if}
                                                {if !isset($oVariationWert0->fAufpreis[1])}
                                                    {assign var=ovw0 value=0}
                                                {else}
                                                    {assign var=ovw0 value=$oVariationWert0->fAufpreis[1]}
                                                {/if}

                                                {math equation='x+y' x=$ovw0 y=$ovw1 assign='fAufpreis'}
                                                {inputgroupaddon append=true}
                                                    {inputgrouptext}
                                                        &times; {gibPreisStringLocalizedSmarty bAufpreise=false fAufpreisNetto=$fAufpreis fVKNetto=$Artikel->Preise->fVKNetto kSteuerklasse=$Artikel->kSteuerklasse nNettoPreise=$NettoPreise fVPEWert=$Artikel->fVPEWert cVPEEinheit=$Artikel->cVPEEinheit FunktionsAttribute=$Artikel->FunktionsAttribute}
                                                    {/inputgrouptext}
                                                {/inputgroupaddon}
                                            {/if}
                                        {/inputgroup}
                                        {if isset($child->nErscheinendesProdukt) && $child->nErscheinendesProdukt == 1}
                                            <div>
                                                <small>
                                                    {lang key='productAvailableFrom'}: <strong>{$child->Erscheinungsdatum_de}</strong>
                                                </small>
                                            </div>
                                        {/if}
                                        <div class="delivery-status">
                                            <small>
                                                {if $Artikel->nIstVater == 1}
                                                    {if isset($child->nErscheinendesProdukt) && !$child->nErscheinendesProdukt}
                                                        {if $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige !== 'nichts'
                                                            && $child->cLagerBeachten === 'Y'
                                                            && ($child->cLagerKleinerNull === 'N'
                                                                || $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U')
                                                            && $child->fLagerbestand <= 0
                                                            && $child->fZulauf > 0
                                                            && isset($child->dZulaufDatum_de)}
                                                            {assign var=cZulauf value=$child->fZulauf|cat:':::'|cat:$child->dZulaufDatum_de}
                                                            <span class="status status-1"><i class="fa fa-truck"></i> {lang key='productInflowing' section='productDetails' printf=$cZulauf}</span>
                                                        {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige !== 'nichts'
                                                            && $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen !== 'N'
                                                            && $child->cLagerBeachten === 'Y'
                                                            && $child->fLagerbestand <= 0
                                                            && $child->fLieferantenlagerbestand > 0
                                                            && $child->fLieferzeit > 0
                                                            && ($child->cLagerKleinerNull === 'N'
                                                                || $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U')}
                                                            <span class="status status-1"><i class="fa fa-truck"></i> {lang key='supplierStockNotice' printf=$child->fLieferzeit}</span>
                                                        {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'verfuegbarkeit'
                                                            || $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'genau'}
                                                            <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->cLagerhinweis[$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige]}</span>
                                                        {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'ampel'}
                                                            <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->AmpelText}</span>
                                                        {/if}
                                                        {include file='productdetails/warehouse.tpl' tplscope='detail'}
                                                    {else}
                                                        {if $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'verfuegbarkeit'
                                                            || $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'genau'
                                                            && ((isset($child->fLagerbestand)
                                                                    && $child->fLagerbestand > 0)
                                                                || (isset($child->cLagerKleinerNull)
                                                                    && $child->cLagerKleinerNull === 'Y'))}
                                                            <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->cLagerhinweis[$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige]}</span>
                                                        {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'ampel'
                                                            && ((isset($child->fLagerbestand)
                                                                    && $child->fLagerbestand > 0)
                                                                || (isset($child->cLagerKleinerNull)
                                                                    && $child->cLagerKleinerNull === 'Y'))}
                                                            <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->AmpelText}</span>
                                                        {/if}
                                                    {/if}
                                                {/if}
                                            </small>
                                        </div>
                                    {/if}
                                </td>
                            {else}
                                <td class="not-available">&nbsp;</td>
                            {/if}
                        {/foreach}
                    </tr>
                {/foreach}
            {/if}
        </tbody>
        {else}{* ****** 1-dimensional ****** *}
            {if $Einstellungen.artikeldetails.artikeldetails_warenkorbmatrix_anzeigeformat === 'Q'
                && $Artikel->VariationenOhneFreifeld[0]->Werte|count <= 10}
                {* QUERFORMAT *}
                <thead>
                <tr>
                    {foreach $Artikel->VariationenOhneFreifeld[0]->Werte as $oVariationWertHead}
                        {if $Einstellungen.global.artikeldetails_variationswertlager != 3
                            || (!isset($oVariationWertHead->nNichtLieferbar)
                                || $oVariationWertHead->nNichtLieferbar != 1)}
                            {assign var=cVariBox value=$oVariationWertHead->kEigenschaft|cat:':'|cat:$oVariationWertHead->kEigenschaftWert}
                            <td class="text-center">
                                {if $Artikel->oVariBoxMatrixBild_arr|@count > 0}
                                    {foreach $Artikel->oVariBoxMatrixBild_arr as $oVariBoxMatrixBild}
                                        {if $oVariBoxMatrixBild->kEigenschaftWert == $oVariationWertHead->kEigenschaftWert}
                                            {image src="{$oVariBoxMatrixBild->cBild}" fluid=true alt=""}<br>
                                        {/if}
                                    {/foreach}
                                {/if}
                                <strong>{$oVariationWertHead->cName}</strong>
                            </td>
                        {/if}
                    {/foreach}
                </tr>
                <thead>
                <tbody>
                <tr>
                    {foreach $Artikel->VariationenOhneFreifeld[0]->Werte as $oVariationWertHead}
                        {if $Einstellungen.global.artikeldetails_variationswertlager != 3
                            || !isset($oVariationWertHead->nNichtLieferbar)
                            || $oVariationWertHead->nNichtLieferbar != 1}
                            {assign var=cVariBox value=$oVariationWertHead->kEigenschaft|cat:':'|cat:$oVariationWertHead->kEigenschaftWert}
                            {if isset($Artikel->oVariationKombiKinderAssoc_arr[$cVariBox])}
                                {assign var=child value=$Artikel->oVariationKombiKinderAssoc_arr[$cVariBox]}
                            {/if}
                            <td class="text-center">
                                {if $Einstellungen.global.global_erscheinende_kaeuflich === 'N'
                                    && isset($child->nErscheinendesProdukt) && $child->nErscheinendesProdukt == 1}
                                    <small>
                                        {lang key='productAvailableFrom'}: <strong>{$child->Erscheinungsdatum_de}</strong>
                                    </small>
                                {elseif isset($oVariationWertHead->nNichtLieferbar)
                                    && $oVariationWertHead->nNichtLieferbar == 1}
                                    {if isset($child->nErscheinendesProdukt) && $child->nErscheinendesProdukt == 1}
                                        <small>
                                            {lang key='productAvailableFrom'}: <strong>{$child->Erscheinungsdatum_de}</strong>
                                        </small>
                                    {else}
                                        {$outofstockInfo}
                                    {/if}
                                {elseif (isset($child->bHasKonfig)
                                    && $child->bHasKonfig == true)
                                    || (isset($child->nVariationAnzahl)
                                        && isset($child->nVariationOhneFreifeldAnzahl)
                                        && $child->nVariationAnzahl > $child->nVariationOhneFreifeldAnzahl)}
                                    {link href="{$child->cSeo}" title="{lang key='configure'} {$oVariationWertHead->cName}" class="btn btn-primary configurepos"}
                                        <i class="fa fa-cogs"></i>
                                        <span class="d-none d-sm-inline-block pl-2">{lang key='configure'}</span>
                                    {/link}
                                    {if isset($child->nErscheinendesProdukt) && $child->nErscheinendesProdukt == 1}
                                        <div>
                                            <small>
                                                {lang key='productAvailableFrom'}: <strong>{$child->Erscheinungsdatum_de}</strong>
                                            </small>
                                        </div>
                                    {/if}
                                    <div class="delivery-status">
                                        <small>
                                            {if !$child->nErscheinendesProdukt}
                                                {if $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige !== 'nichts'
                                                    && $child->cLagerBeachten === 'Y'
                                                    && ($child->cLagerKleinerNull === 'N'
                                                        || $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U')
                                                    && $child->fLagerbestand <= 0
                                                    && $child->fZulauf > 0
                                                    && isset($child->dZulaufDatum_de)}
                                                    {assign var=cZulauf value=$child->fZulauf|cat:':::'|cat:$child->dZulaufDatum_de}
                                                    <span class="status status-1"><i class="fa fa-truck"></i> {lang key='productInflowing' section='productDetails' printf=$cZulauf}</span>
                                                {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige !== 'nichts'
                                                    && $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen !== 'N'
                                                    && $child->cLagerBeachten === 'Y'
                                                    && $child->fLagerbestand <= 0
                                                    && $child->fLieferantenlagerbestand > 0
                                                    && $child->fLieferzeit > 0
                                                    && ($child->cLagerKleinerNull === 'N'
                                                        || $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U')}
                                                    <span class="status status-1"><i class="fa fa-truck"></i> {lang key='supplierStockNotice' printf=$child->fLieferzeit}</span>
                                                {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'verfuegbarkeit'
                                                    || $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'genau'}
                                                    <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->cLagerhinweis[$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige]}</span>
                                                {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'ampel'}
                                                    <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->AmpelText}</span>
                                                {/if}
                                                {include file='productdetails/warehouse.tpl' tplscope='detail'}
                                            {else}
                                                {if $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'verfuegbarkeit'
                                                    || $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'genau'
                                                    && ($child->fLagerbestand > 0
                                                        || $child->cLagerKleinerNull === 'Y')}
                                                    <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->cLagerhinweis[$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige]}</span>
                                                {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'ampel'
                                                    && ($child->fLagerbestand > 0
                                                        || $child->cLagerKleinerNull === 'Y')}
                                                    <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->AmpelText}</span>
                                                {/if}
                                            {/if}
                                        </small>
                                    </div>
                                {else}
                                    {inputgroup class="{if isset($smarty.session.variBoxAnzahl_arr[$cVariBox]->bError) && $smarty.session.variBoxAnzahl_arr[$cVariBox]->bError}has-error{/if}"}
                                        {input class="text-right{if isset($smarty.session.variBoxAnzahl_arr[$cVariBox]->bError) && $smarty.session.variBoxAnzahl_arr[$cVariBox]->bError} bg-danger{/if}"
                                            placeholder="0"
                                            name="variBoxAnzahl[_{$oVariationWertHead->kEigenschaft}:{$oVariationWertHead->kEigenschaftWert}]"
                                            aria=["label"=>"{lang key='quantity'} {$oVariationWertHead->cName}"]
                                            type="text"
                                            value="{if isset($smarty.session.variBoxAnzahl_arr[$cVariBox]->fAnzahl)}{$smarty.session.variBoxAnzahl_arr[$cVariBox]->fAnzahl|replace_delim}{/if}"}
                                        {if $Artikel->nVariationAnzahl == 1 && ($Artikel->kVaterArtikel > 0 || $Artikel->nIstVater == 1)}
                                            {assign var=kEigenschaftWert value=$oVariationWertHead->kEigenschaftWert}
                                            {inputgroupaddon append=true}
                                                {inputgrouptext}&times; {$Artikel->oVariationDetailPreis_arr[$kEigenschaftWert]->Preise->cVKLocalized[$NettoPreise]}{if isset($Artikel->oVariationDetailPreis_arr[$kEigenschaftWert]->Preise->PreisecPreisVPEWertInklAufpreis[$NettoPreise]) && !empty($Artikel->oVariationDetailPreis_arr[$kEigenschaftWert]->Preise->PreisecPreisVPEWertInklAufpreis[$NettoPreise])} <small>({$Artikel->oVariationDetailPreis_arr[$kEigenschaftWert]->Preise->PreisecPreisVPEWertInklAufpreis[$NettoPreise]}){/if}</small>{/inputgrouptext}
                                            {/inputgroupaddon}
                                        {elseif $Einstellungen.artikeldetails.artikel_variationspreisanzeige == 1 && $oVariationWertHead->fAufpreisNetto != 0}
                                            {inputgroupaddon append=true}
                                                {inputgrouptext}{$oVariationWertHead->cAufpreisLocalized[$NettoPreise]}{if !empty($oVariationWertHead->cPreisVPEWertAufpreis[$NettoPreise])} <small>({$oVariationWertHead->cPreisVPEWertAufpreis[$NettoPreise]})</small>{/if}{/inputgrouptext}
                                            {/inputgroupaddon}
                                        {elseif $Einstellungen.artikeldetails.artikel_variationspreisanzeige == 2 && $oVariationWertHead->fAufpreisNetto != 0}
                                            {inputgroupaddon append=true}
                                                {inputgrouptext}&times; {$oVariationWertHead->cPreisInklAufpreis[$NettoPreise]}{if !empty($oVariationWertHead->cPreisVPEWertInklAufpreis[$NettoPreise])} <small>({$oVariationWertHead->cPreisVPEWertInklAufpreis[$NettoPreise]})</small>{/if}{/inputgrouptext}
                                            {/inputgroupaddon}
                                        {/if}
                                    {/inputgroup}
                                    {if isset($child->nErscheinendesProdukt) && $child->nErscheinendesProdukt == 1}
                                        <div>
                                            <small>
                                                {lang key='productAvailableFrom'}: <strong>{$child->Erscheinungsdatum_de}</strong>
                                            </small>
                                        </div>
                                    {/if}
                                    <div class="delivery-status">
                                        <small>
                                            {if $Artikel->nIstVater == 1}
                                                {if isset($child->nErscheinendesProdukt) && !$child->nErscheinendesProdukt}
                                                    {if $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige !== 'nichts'
                                                        && $child->cLagerBeachten === 'Y'
                                                        && ($child->cLagerKleinerNull === 'N'
                                                            || $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U')
                                                        && $child->fLagerbestand <= 0
                                                        && $child->fZulauf > 0
                                                        && isset($child->dZulaufDatum_de)}
                                                        {assign var=cZulauf value=$child->fZulauf|cat:':::'|cat:$child->dZulaufDatum_de}
                                                        <span class="status status-1"><i class="fa fa-truck"></i> {lang key='productInflowing' section='productDetails' printf=$cZulauf}</span>
                                                    {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige !== 'nichts'
                                                        && $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen !== 'N'
                                                        && $child->cLagerBeachten === 'Y'
                                                        && $child->fLagerbestand <= 0
                                                        && $child->fLieferantenlagerbestand > 0
                                                        && $child->fLieferzeit > 0
                                                        && ($child->cLagerKleinerNull === 'N'
                                                            || $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U')}
                                                        <span class="status status-1"><i class="fa fa-truck"></i> {lang key='supplierStockNotice' printf=$child->fLieferzeit}</span>
                                                    {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'verfuegbarkeit'
                                                        || $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'genau'}
                                                        <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->cLagerhinweis[$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige]}</span>
                                                    {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'ampel'}
                                                        <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->AmpelText}</span>
                                                    {/if}
                                                    {include file='productdetails/warehouse.tpl' tplscope='detail'}
                                                {else}
                                                    {if $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'verfuegbarkeit'
                                                        || $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'genau'
                                                        && ($child->fLagerbestand > 0
                                                            || $child->cLagerKleinerNull === 'Y')}
                                                        <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->cLagerhinweis[$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige]}</span>
                                                    {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'ampel'
                                                        && ((isset($child->fLagerbestand)
                                                                && $child->fLagerbestand > 0)
                                                            || (isset($child->cLagerKleinerNull)
                                                                && $child->cLagerKleinerNull === 'Y'))}
                                                        <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->AmpelText}</span>
                                                    {/if}
                                                {/if}
                                            {/if}
                                        </small>
                                    </div>
                                {/if}
                            </td>
                        {/if}
                    {/foreach}
                </tr>
                </tbody>
            {else}
                {* HOCHFORMAT *}
                <tbody>
                {foreach $Artikel->VariationenOhneFreifeld[0]->Werte as $oVariationWertHead}
                    {if $Einstellungen.global.artikeldetails_variationswertlager != 3
                        || (!isset($oVariationWertHead->nNichtLieferbar)
                            || $oVariationWertHead->nNichtLieferbar != 1)}
                        {assign var=cVariBox value=$oVariationWertHead->kEigenschaft|cat:':'|cat:$oVariationWertHead->kEigenschaftWert}
                        {if isset($Artikel->oVariationKombiKinderAssoc_arr[$cVariBox])}
                            {assign var=child value=$Artikel->oVariationKombiKinderAssoc_arr[$cVariBox]}
                        {/if}
                        <tr>
                            <td class="text-center">
                                {if $Artikel->oVariBoxMatrixBild_arr|@count > 0}
                                    {foreach $Artikel->oVariBoxMatrixBild_arr as $oVariBoxMatrixBild}
                                        {if $oVariBoxMatrixBild->kEigenschaftWert == $oVariationWertHead->kEigenschaftWert}
                                            {image src="{$oVariBoxMatrixBild->cBild}" fluid=true alt=""}<br>
                                        {/if}
                                    {/foreach}
                                {/if}
                                <strong> {$oVariationWertHead->cName}</strong>
                            </td>
                            <td class="form-inline">
                                {if $Einstellungen.global.global_erscheinende_kaeuflich === 'N'
                                    && isset($child->nErscheinendesProdukt)
                                    && $child->nErscheinendesProdukt == 1}
                                    <small>
                                        {lang key='productAvailableFrom'}: <strong>{$child->Erscheinungsdatum_de}</strong>
                                    </small>
                                {elseif isset($oVariationWertHead->nNichtLieferbar) && $oVariationWertHead->nNichtLieferbar == 1}
                                    {if isset($child->nErscheinendesProdukt) && $child->nErscheinendesProdukt == 1}
                                        <small>
                                            {lang key='productAvailableFrom'}: <strong>{$child->Erscheinungsdatum_de}</strong>
                                        </small>
                                    {else}
                                        {$outofstockInfo}
                                    {/if}
                                {elseif (isset($child->bHasKonfig)
                                        && $child->bHasKonfig == true)
                                    || (isset($child->nVariationAnzahl)
                                        && isset($child->nVariationOhneFreifeldAnzahl)
                                        && $child->nVariationAnzahl > $child->nVariationOhneFreifeldAnzahl)}
                                    {link href="{$child->cSeo}" title="{lang key='configure'} {$oVariationWertHead->cName}" class="btn btn-primary configurepos"}
                                        <i class="fa fa-cogs"></i>
                                        <span class="d-none d-sm-inline-block pl-2">{lang key='configure'}</span>
                                    {/link}
                                    {if isset($child->nErscheinendesProdukt) && $child->nErscheinendesProdukt == 1}
                                        <div>
                                            <small>
                                                {lang key='productAvailableFrom'}: <strong>{$child->Erscheinungsdatum_de}</strong>
                                            </small>
                                        </div>
                                    {/if}
                                    <div class="delivery-status">
                                        <small>
                                            {if !$child->nErscheinendesProdukt}
                                                {if $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige !== 'nichts'
                                                && $child->cLagerBeachten === 'Y'
                                                && ($child->cLagerKleinerNull === 'N'
                                                    || $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U')
                                                && $child->fLagerbestand <= 0
                                                && $child->fZulauf > 0
                                                && isset($child->dZulaufDatum_de)}
                                                    {assign var=cZulauf value=$child->fZulauf|cat:':::'|cat:$child->dZulaufDatum_de}
                                                    <span class="status status-1"><i class="fa fa-truck"></i> {lang key='productInflowing' section='productDetails' printf=$cZulauf}</span>
                                                {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige !== 'nichts'
                                                    && $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen !== 'N'
                                                    && $child->cLagerBeachten === 'Y'
                                                    && $child->fLagerbestand <= 0
                                                    && $child->fLieferantenlagerbestand > 0
                                                    && $child->fLieferzeit > 0
                                                    && ($child->cLagerKleinerNull === 'N'
                                                        || $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U')}
                                                    <span class="status status-1"><i class="fa fa-truck"></i> {lang key='supplierStockNotice' printf=$child->fLieferzeit}</span>
                                                {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'verfuegbarkeit'
                                                    || $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'genau'}
                                                    <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->cLagerhinweis[$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige]}</span>
                                                {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'ampel'}
                                                    <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->AmpelText}</span>
                                                {/if}
                                                {include file='productdetails/warehouse.tpl' tplscope='detail'}
                                            {else}
                                                {if $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'verfuegbarkeit'
                                                    || $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'genau'
                                                    && ($child->fLagerbestand > 0
                                                        || $child->cLagerKleinerNull === 'Y')}
                                                    <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->cLagerhinweis[$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige]}</span>
                                                {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'ampel'
                                                    && ((isset($child->fLagerbestand)
                                                            && $child->fLagerbestand > 0)
                                                        || (isset($child->cLagerKleinerNull)
                                                            && $child->cLagerKleinerNull === 'Y'))}
                                                    <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->AmpelText}</span>
                                                {/if}
                                            {/if}
                                        </small>
                                    </div>
                                {else}
                                    {inputgroup class="{if isset($smarty.session.variBoxAnzahl_arr[$cVariBox]->bError) && $smarty.session.variBoxAnzahl_arr[$cVariBox]->bError}has-error{/if}"}
                                        {input
                                            class="text-right" placeholder="0"
                                            name="variBoxAnzahl[_{$oVariationWertHead->kEigenschaft}:{$oVariationWertHead->kEigenschaftWert}]"
                                            aria=["label"=>"{lang key='quantity'} {$oVariationWertHead->cName}"]
                                            type="text"
                                            value="{if isset($smarty.session.variBoxAnzahl_arr[$cVariBox]->fAnzahl)}{$smarty.session.variBoxAnzahl_arr[$cVariBox]->fAnzahl|replace_delim}{/if}"}
                                        {if $Artikel->nVariationAnzahl == 1 && ($Artikel->kVaterArtikel > 0 || $Artikel->nIstVater == 1)}
                                            {assign var=kEigenschaftWert value=$oVariationWertHead->kEigenschaftWert}
                                            {inputgroupaddon append=true}
                                                {inputgrouptext}
                                                    &times; {$Artikel->oVariationDetailPreis_arr[$kEigenschaftWert]->Preise->cVKLocalized[$NettoPreise]}{if isset($Artikel->oVariationDetailPreis_arr[$kEigenschaftWert]->Preise->PreisecPreisVPEWertInklAufpreis[$NettoPreise]) && !empty($Artikel->oVariationDetailPreis_arr[$kEigenschaftWert]->Preise->PreisecPreisVPEWertInklAufpreis[$NettoPreise])} <small>({$Artikel->oVariationDetailPreis_arr[$kEigenschaftWert]->Preise->PreisecPreisVPEWertInklAufpreis[$NettoPreise]})</small>{/if}
                                                {/inputgrouptext}
                                            {/inputgroupaddon}
                                        {elseif $Einstellungen.artikeldetails.artikel_variationspreisanzeige == 1 && $oVariationWertHead->fAufpreisNetto!=0}
                                            {inputgroupaddon append=true}
                                                {inputgrouptext}
                                                    {$oVariationWertHead->cAufpreisLocalized[$NettoPreise]}{if !empty($oVariationWertHead->cPreisVPEWertAufpreis[$NettoPreise])} <small>({$oVariationWertHead->cPreisVPEWertAufpreis[$NettoPreise]})</small>{/if}
                                                {/inputgrouptext}
                                            {/inputgroupaddon}
                                        {elseif $Einstellungen.artikeldetails.artikel_variationspreisanzeige == 2 && $oVariationWertHead->fAufpreisNetto!=0}
                                            {inputgroupaddon append=true}
                                                {inputgrouptext}
                                                    &times; {$oVariationWertHead->cPreisInklAufpreis[$NettoPreise]}{if !empty($oVariationWertHead->cPreisVPEWertInklAufpreis[$NettoPreise])} <small>({$oVariationWertHead->cPreisVPEWertInklAufpreis[$NettoPreise]})</small>{/if}
                                                {/inputgrouptext}
                                            {/inputgroupaddon}
                                        {/if}
                                    {/inputgroup}
                                    {if isset($child->nErscheinendesProdukt) && $child->nErscheinendesProdukt == 1}
                                        <div>
                                            <small>
                                                {lang key='productAvailableFrom'}: <strong>{$child->Erscheinungsdatum_de}</strong>
                                            </small>
                                        </div>
                                    {/if}
                                    <div class="delivery-status ml-3">
                                        <small>
                                            {if $Artikel->nIstVater == 1}
                                                {if isset($child->nErscheinendesProdukt) && !$child->nErscheinendesProdukt}
                                                    {if $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige !== 'nichts'
                                                    && $child->cLagerBeachten === 'Y'
                                                    && ($child->cLagerKleinerNull === 'N'
                                                        || $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U')
                                                    && $child->fLagerbestand <= 0
                                                    && $child->fZulauf > 0
                                                    && isset($child->dZulaufDatum_de)}
                                                        {assign var=cZulauf value=$child->fZulauf|cat:':::'|cat:$child->dZulaufDatum_de}
                                                        <span class="status status-1"><i class="fa fa-truck"></i> {lang key='productInflowing' section='productDetails' printf=$cZulauf}</span>
                                                    {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige !== 'nichts'
                                                        && $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen !== 'N'
                                                        && $child->cLagerBeachten === 'Y'
                                                        && $child->fLagerbestand <= 0
                                                        && $child->fLieferantenlagerbestand > 0
                                                        && $child->fLieferzeit > 0
                                                        && ($child->cLagerKleinerNull === 'N'
                                                            || $Einstellungen.artikeldetails.artikeldetails_lieferantenbestand_anzeigen === 'U')}
                                                        <span class="status status-1"><i class="fa fa-truck"></i> {lang key='supplierStockNotice' printf=$child->fLieferzeit}</span>
                                                    {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'verfuegbarkeit'
                                                        || $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'genau'}
                                                        <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->cLagerhinweis[$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige]}</span>
                                                    {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'ampel'}
                                                        <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->AmpelText}</span>
                                                    {/if}
                                                    {include file='productdetails/warehouse.tpl' tplscope='detail'}
                                                {else}
                                                    {if $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'verfuegbarkeit'
                                                        || $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'genau'
                                                        && ((isset($child->fLagerbestand)
                                                                && $child->fLagerbestand > 0)
                                                            || (isset($child->cLagerKleinerNull)
                                                                && $child->cLagerKleinerNull === 'Y'))}
                                                        <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->cLagerhinweis[$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige]}</span>
                                                    {elseif $Einstellungen.artikeldetails.artikel_lagerbestandsanzeige === 'ampel'
                                                        && ((isset($child->fLagerbestand)
                                                                && $child->fLagerbestand > 0)
                                                            || (isset($child->cLagerKleinerNull)
                                                                && $child->cLagerKleinerNull === 'Y'))}
                                                        <span class="status status-{$child->Lageranzeige->nStatus}"><i class="fa fa-truck"></i> {$child->Lageranzeige->AmpelText}</span>
                                                    {/if}
                                                {/if}
                                            {/if}
                                        </small>
                                    </div>
                                {/if}
                            </td>
                        </tr>
                    {/if}
                {/foreach}
                </tbody>
            {/if}
        {/if}
    </table>
</div>
{input type="hidden" name="variBox" value="1"}
{button name="inWarenkorb" type="submit" value="{lang key='addToCart'}" variant="primary" class="float-right"}{lang key='addToCart'}{/button}
