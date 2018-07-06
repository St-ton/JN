{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
<script type="text/javascript">
    if (top.location !== self.location) {ldelim}
        top.location = self.location.href;
    {rdelim}
</script>

{if !empty($cFehler)}
    <div class="alert alert-danger">{$cFehler}</div>
{/if}

{include file='snippets/extension.tpl'}

<h1>{lang key='orderCompletedPre' section='checkout'}</h1>

<div class="row">
    <div class="col-xs-12">
        {block name='order-details-order-info'}
        <ul class="list-group">
            <li class="list-group-item"><strong>{lang key='yourOrderId' section='checkout'}:</strong> {$Bestellung->cBestellNr}</li>
            <li class="list-group-item"><strong>{lang key='orderDate' section='login'}:</strong> {$Bestellung->dErstelldatum_de}</li>
            <li class="list-group-item alert-info"><strong>Status:</strong> {$Bestellung->Status}</li>
        </ul>
        {/block}
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-md-6">
        {block name='order-details-billing-address'}
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">{block name='order-details-billing-address-title'}{lang key='billingAdress' section='checkout'}{/block}</h3></div>
            <div class="panel-body">
                {include file='checkout/inc_billing_address.tpl'}
            </div>
        </div>
        {/block}
    </div>
    <div class="col-xs-12 col-md-6">
        {block name='order-details-shipping-address'}
        <div class="panel panel-default">
            {if !empty($Lieferadresse->kLieferadresse)}
                <div class="panel-heading"><h3 class="panel-title">{block name='order-details-shipping-address-title'}{lang key='shippingAdress' section='checkout'}{/block}</h3></div>
                <div class="panel-body">
                    {include file='checkout/inc_delivery_address.tpl'}
                </div>
            {else}
                <div class="panel-heading"><h3 class="panel-title">{block name='order-details-shipping-address-title'}{lang key='shippingAdressEqualBillingAdress' section='account data'}{/block}</h3></div>
                <div class="panel-body">
                    {include file='checkout/inc_billing_address.tpl'}
                </div>
            {/if}
        </div>
        {/block}
    </div>
</div>
<div class="row">
    <div class="col-xs-12 col-md-6">
        <div class="panel panel-default">
            {block name='order-details-payment'}
            <div class="panel-heading"><h3 class="panel-title">{block name='order-details-payment-title'}{lang key='paymentOptions' section='global'}: {$Bestellung->cZahlungsartName}{/block}</h3></div>
            <div class="panel-body">
            {block name='order-details-payment-body'}
            {if $Bestellung->cStatus != BESTELLUNG_STATUS_STORNO && $Bestellung->dBezahldatum_de !== '00.00.0000'}
                {lang key='payedOn' section='login'} {$Bestellung->dBezahldatum_de}
            {else}
                {if ($Bestellung->cStatus == BESTELLUNG_STATUS_OFFEN || $Bestellung->cStatus == BESTELLUNG_STATUS_IN_BEARBEITUNG) && (($Bestellung->Zahlungsart->cModulId !== 'za_ueberweisung_jtl' && $Bestellung->Zahlungsart->cModulId !== 'za_nachnahme_jtl' && $Bestellung->Zahlungsart->cModulId !== 'za_rechnung_jtl' && $Bestellung->Zahlungsart->cModulId !== 'za_barzahlung_jtl' && $Bestellung->Zahlungsart->cModulId !== 'za_billpay_jtl') && (isset($Bestellung->Zahlungsart->bPayAgain) && $Bestellung->Zahlungsart->bPayAgain))}
                    <a href="bestellab_again.php?kBestellung={$Bestellung->kBestellung}">{lang key='payNow' section='global'}</a>
                {else}
                    {lang key='notPayedYet' section='login'}
                {/if}
            {/if}
            {/block}
            </div>
            {/block}
        </div>
    </div>
    <div class="col-xs-12 col-md-6">
        <div class="panel panel-default">
            {block name='order-details-shipping'}
            <div class="panel-heading"><h3 class="panel-title">{block name='order-details-shipping-title'}{lang key='shippingOptions' section='global'}: {$Bestellung->cVersandartName}{/block}</h3></div>
            <div class="panel-body">
            {block name='order-details-shipping-body'}
            {if $Bestellung->cStatus == BESTELLUNG_STATUS_VERSANDT}
                {lang key='shippedOn' section='login'} {$Bestellung->dVersanddatum_de}
            {elseif $Bestellung->cStatus == BESTELLUNG_STATUS_TEILVERSANDT}
                {$Bestellung->Status}
            {else}
                <p>{lang key='notShippedYet' section='login'}</p>
                {if $Bestellung->cStatus != BESTELLUNG_STATUS_STORNO}
                <p><strong>{lang key='shippingTime' section='global'}</strong>: {if isset($cEstimatedDeliveryEx)}{$cEstimatedDeliveryEx}{else}{$Bestellung->cEstimatedDelivery}{/if}</p>
                {/if}
            {/if}
            {/block}
            </div>
            {/block}
        </div>
    </div>
</div>

{block name='order-details-basket'}
<h2>{lang key='basket'}</h2>
<div class="table-responsive">
    <table class="table table-striped table-bordered" id="customerorder">
        <thead>
            <tr>
                <th>{lang key='product' section='global'}</th>
                <th>{lang key='shippingStatus' section='login'}</th>
                <th class="text-right">{lang key='quantity' section='checkout'}</th>
                <th class="text-right">{lang key='merchandiseValue' section='checkout'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach $Bestellung->Positionen as $Position}
                {if !($Position->cUnique|strlen > 0 && $Position->kKonfigitem > 0)}
                    <tr>
                        <td>
                            {include file='account/order_item.tpl' Position=$Position bPreis=true bKonfig=true}
                        </td>
                        <td>
                            {if $Position->nPosTyp == 1}
                                {if $Position->bAusgeliefert}
                                    {lang key='statusShipped' section='order'}
                                {elseif $Position->nAusgeliefert > 0}
                                    {if $Position->cUnique|strlen == 0}{lang key='statusShipped' section='order'}: {$Position->nAusgeliefertGesamt}{else}{lang key='statusPartialShipped' section='order'}{/if}
                                {else}
                                    {lang key='notShippedYet' section='login'}
                                {/if}
                            {/if}
                        </td>
                        <td class="text-right">
                            {$Position->nAnzahl|replace_delim}
                        </td>
                        <td class="text-right">
                            {if $Position->cUnique|strlen > 0 && $Position->kKonfigitem == 0}
                                <p>{$Position->cKonfigpreisLocalized[$NettoPreise]}</p>
                            {else}
                                <p>{$Position->cGesamtpreisLocalized[$NettoPreise]}</p>
                            {/if}
                        </td>
                    </tr>
                {/if}
            {/foreach}
        </tbody>
        <tfoot>
            {if $NettoPreise}
                <tr>
                    <td colspan="3" class="text-right"><span class="price_label">{lang key='totalSum' section='global'}:</span></td>
                    <td class="text-right"><span>{$Bestellung->WarensummeLocalized[$NettoPreise]}</span></td>
                </tr>
            {/if}
            {if $Einstellungen.global.global_steuerpos_anzeigen !== 'N'}
                {foreach $Bestellung->Steuerpositionen as $taxPosition}
                    <tr>
                        <td colspan="3" class="text-right">{$taxPosition->cName}</td>
                        <td class="text-right">{$taxPosition->cPreisLocalized}</td>
                    </tr>
                {/foreach}
            {/if}
            {if $Bestellung->GuthabenNutzen == 1}
                <tr>
                    <td colspan="3" class="text-right"><span class="price_label">{lang key='useCredit' section='account data'}:</span></td>
                    <td class="text-right">{$Bestellung->GutscheinLocalized}</span></td>
                </tr>
            {/if}
            <tr class="info">
                <td colspan="3" class="text-right"><span class="price_label"><strong>{lang key='totalSum' section='global'}</strong>{if $NettoPreise} {lang key='gross' section='global'}{/if}:</span></td>
                <td class="text-right"><span class="price">{$Bestellung->WarensummeLocalized[0]}</span></td>
            </tr>
        </tfoot>
    </table>
</div>

{include file='account/downloads.tpl'}
{include file='account/uploads.tpl'}
{/block}

{if $Bestellung->oLieferschein_arr|@count > 0}
{block name='order-details-delivery-note'}
    <h2>{if $Bestellung->cStatus == BESTELLUNG_STATUS_TEILVERSANDT}{lang key='partialShipped' section='order'}{else}{lang key='shipped' section='order'}{/if}</h2>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>{lang key='shippingOrder' section='order'}</th>
                    <th>{lang key='shippedOn' section='login'}</th>
                    <th class="text-right">{lang key='packageTracking' section='order'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach $Bestellung->oLieferschein_arr as $oLieferschein}
                    <tr>
                        <td><a class="popup-dep" id="{$oLieferschein->getLieferschein()}" href="#" title="{$oLieferschein->getLieferscheinNr()}">{$oLieferschein->getLieferscheinNr()}</a></td>
                        <td>{$oLieferschein->getErstellt()|date_format:"%d.%m.%Y %H:%M"}</td>
                        <td class="text-right">{foreach $oLieferschein->oVersand_arr as $oVersand}{if $oVersand->getIdentCode()}<p><a href="{$oVersand->getLogistikVarUrl()}" target="_blank" class="shipment" title="{$oVersand->getIdentCode()}">{lang key='packageTracking' section='order'}</a></p>{/if}{/foreach}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>

    {* Lieferschein Popups *}
    {foreach $Bestellung->oLieferschein_arr as $oLieferschein}
        {block name='order-details-delivery-note-popup'}
        <div id="popup{$oLieferschein->getLieferschein()}" class="hidden">
            <h1>{if $Bestellung->cStatus == BESTELLUNG_STATUS_TEILVERSANDT}{lang key='partialShipped' section='order'}{else}{lang key='shipped' section='order'}{/if}</h1>
            <div class="well well-sm">
                <strong>{lang key='shippingOrder' section='order'}</strong>: {$oLieferschein->getLieferscheinNr()}<br />
                <strong>{lang key='shippedOn' section='login'}</strong>: {$oLieferschein->getErstellt()|date_format:"%d.%m.%Y %H:%M"}<br />
            </div>

            {if $oLieferschein->getHinweis()|@count_characters > 0}
                <div class="alert alert-info">
                    {$oLieferschein->getHinweis()}
                </div>
            {/if}

            <div class="well well-sm">
                {foreach $oLieferschein->oVersand_arr as $oVersand}{if $oVersand->getIdentCode()}<p><a href="{$oVersand->getLogistikVarUrl()}" target="_blank" class="shipment" title="{$oVersand->getIdentCode()}">{lang key='packageTracking' section='order'}</a></p>{/if}{/foreach}
            </div>

            <div class="well well-sm">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{lang key='partialShippedPosition' section='order'}</th>
                            <th>{lang key='partialShippedCount' section='order'}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $oLieferschein->oLieferscheinPos_arr as $oLieferscheinpos}
                            <tr>
                                <td>{include file='account/order_item.tpl' Position=$oLieferscheinpos->oPosition bPreis=false bKonfig=false}</td>
                                <td>{$oLieferscheinpos->getAnzahl()}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
        {/block}
    {/foreach}
{/block}
{/if}

{if $Bestellung->cKommentar}
    <h3>{lang key='yourOrderComment' section='login'}</h3>
    <p>{$Bestellung->cKommentar}</p>
{/if}

{if !empty($oTrustedShopsBewertenButton->cPicURL)}
    <a href="{$oTrustedShopsBewertenButton->cURL}" target="_blank"><img src="{$oTrustedShopsBewertenButton->cPicURL}" /></a>
{/if}