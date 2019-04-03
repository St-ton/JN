{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<script type="text/javascript">
    if (top.location !== self.location) {ldelim}
        top.location = self.location.href;
    {rdelim}
</script>

<h1>{lang key='orderCompletedPre' section='checkout'}</h1>
{card no-body=true class='mb-3'}
    {cardheader}
        {row}
            {col md=3 class='border-right'}
                <strong><i class="far fa-calendar-alt"></i> {$Bestellung->dErstelldatum_de}</strong>
            {/col}
            {col md=5}
                {lang key='yourOrderId' section='checkout'}: {$Bestellung->cBestellNr}
            {/col}
            {col md=4}
                {lang key='orderStatus' section='login'}: {$Bestellung->Status}
            {/col}
        {/row}
    {/cardheader}
    {cardbody}
        {row}
            {col md=3 class='border-right'}

                {row class='mb-3'}<strong>{lang key='orderOverview' section='account data'}</strong>{/row}
                {row}
                    {col md=8 class='p-0'}<span class="price_label">{lang key='subtotal' section='account data'}:</span>{/col}
                    {col md=4 class='p-0 pr-2 text-right'}<span>{$Bestellung->WarensummeLocalized[1]}</span>{/col}
                {/row}
                {if $Bestellung->GuthabenNutzen == 1}
                    {row}
                    {col md=8  class='p-0'}<span class="price_label">{lang key='useCredit' section='account data'}:</span>{/col}
                    {col md=4  class='p-0 pr-2 text-right'}<span>{$Bestellung->GutscheinLocalized}</span>{/col}
                    {/row}
                {/if}
                {row class='info'}
                    {col md=8 class='p-0'}<span class="price_label"><strong>{lang key='totalSum' section='global'}</strong>{if $NettoPreise} {lang key='gross' section='global'}{/if}:</span>{/col}
                    {col md=4 class='p-0 pr-2 text-right'}<span class="price"><strong>{$Bestellung->WarensummeLocalized[0]}</strong></span>{/col}
                {/row}
                {if $Einstellungen.global.global_steuerpos_anzeigen !== 'N'}
                    {foreach $Bestellung->Steuerpositionen as $taxPosition}
                        {row class='text-muted'}
                            {col md=8 class='p-0'}<small>{$taxPosition->cName}</small>{/col}
                            {col md=4 class='p-0 pr-2 text-right'}<small>{$taxPosition->cPreisLocalized}</small>{/col}
                        {/row}
                    {/foreach}
                {/if}
                <hr class="mt-5 mb-5">
                {row class='mb-3'}
                {block name='order-details-payment'}
                    {col md=12 sm=6 class='p-0'}
                    {block name='order-details-payment-title'}{lang key='paymentOptions' section='global'}: <small>{$Bestellung->cZahlungsartName}</small>{/block}<br />
                    {/col}
                    {col md=12 sm=6 class='p-0'}
                    {block name='order-details-payment-body'}
                        <small>
                        {if $Bestellung->cStatus != BESTELLUNG_STATUS_STORNO && $Bestellung->dBezahldatum_de !== '00.00.0000'}
                            {lang key='payedOn' section='login'} {$Bestellung->dBezahldatum_de}
                        {else}
                            {if ($Bestellung->cStatus == BESTELLUNG_STATUS_OFFEN || $Bestellung->cStatus == BESTELLUNG_STATUS_IN_BEARBEITUNG) && (($Bestellung->Zahlungsart->cModulId !== 'za_ueberweisung_jtl' && $Bestellung->Zahlungsart->cModulId !== 'za_nachnahme_jtl' && $Bestellung->Zahlungsart->cModulId !== 'za_rechnung_jtl' && $Bestellung->Zahlungsart->cModulId !== 'za_barzahlung_jtl') && (isset($Bestellung->Zahlungsart->bPayAgain) && $Bestellung->Zahlungsart->bPayAgain))}
                                {link href="bestellab_again.php?kBestellung={$Bestellung->kBestellung}"}{lang key='payNow' section='global'}{/link}
                            {else}
                                {lang key='notPayedYet' section='login'}
                            {/if}
                        {/if}
                        </small>
                    {/block}
                    {/col}
                {/block}
                {/row}
                {row class='mb-3'}
                {block name='order-details-shipping'}
                    {col md=12 sm=6 class='p-0'}
                    {block name='order-details-shipping-title'}{lang key='shippingOptions' section='global'}: <small>{$Bestellung->cVersandartName}</small>{/block}<br />
                    {/col}
                    {col md=12 sm=6 class='p-0'}
                    {block name='order-details-shipping-body'}
                        <small>
                        {if $Bestellung->cStatus == BESTELLUNG_STATUS_VERSANDT}
                            {lang key='shippedOn' section='login'} {$Bestellung->dVersanddatum_de}
                        {elseif $Bestellung->cStatus == BESTELLUNG_STATUS_TEILVERSANDT}
                            {$Bestellung->Status}
                        {else}
                            <span>{lang key='notShippedYet' section='login'}</span><br />
                            {if $Bestellung->cStatus != BESTELLUNG_STATUS_STORNO}
                                <span>{lang key='shippingTime' section='global'}: {if isset($cEstimatedDeliveryEx)}{$cEstimatedDeliveryEx}{else}{$Bestellung->cEstimatedDelivery}{/if}</span>
                            {/if}
                        {/if}
                        </small>
                    {/block}
                    {/col}
                {/block}
                {/row}
                {row class='mb-3'}
                {block name='order-details-billing-address'}
                    {col md=12 sm=6 class='p-0'}
                    {block name='order-details-billing-address-title'}{lang key='billingAdress' section='checkout'}:{/block}<br />
                    {/col}
                    {col md=12 sm=6 class='p-0'}
                    <small>
                        {include file='checkout/inc_billing_address.tpl' orderDetail=true}
                    </small>
                    {/col}
                {/block}
                {/row}
                {row class='mb-3'}
                {block name='order-details-shipping-address'}
                    {col md=12 sm=6 class='p-0'}
                    {block name='order-details-shipping-address-title'}{lang key='shippingAdress' section='checkout'}:{/block}<br />
                    {/col}
                    {col md=12 sm=6 class='p-0'}
                    <small>
                    {if !empty($Lieferadresse->kLieferadresse)}
                        {include file='checkout/inc_delivery_address.tpl' orderDetail=true}
                    {else}
                        {block name='order-details-shipping-address-title'}{lang key='shippingAdressEqualBillingAdress' section='account data'}{/block}
                    {/if}
                    </small>
                    {/col}
                {/block}
                {/row}
            {/col}
            {col md=9}
                <strong>{lang key='basket'}</strong>
                {include file='account/order_item.tpl' tplscope='confirmation'}
            {/col}
        {/row}
    {/cardbody}
{/card}

{block name='order-details-basket'}
{include file='account/downloads.tpl'}
{include file='account/uploads.tpl'}
{/block}

{if $Bestellung->oLieferschein_arr|@count > 0}
{block name='order-details-delivery-note'}
    <div class="h2">{if $Bestellung->cStatus == BESTELLUNG_STATUS_TEILVERSANDT}{lang key='partialShipped' section='order'}{else}{lang key='shipped' section='order'}{/if}</div>
    <div class="table-responsive mb-3">
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
                        <td>{link class="popup-dep" id=$oLieferschein->getLieferschein() href="#" title=$oLieferschein->getLieferscheinNr()}{$oLieferschein->getLieferscheinNr()}{/link}</td>
                        <td>{$oLieferschein->getErstellt()|date_format:"%d.%m.%Y %H:%M"}</td>
                        <td class="text-right">
                            {foreach $oLieferschein->oVersand_arr as $oVersand}
                                {if $oVersand->getIdentCode()}
                                    <p>{link href=$oVersand->getLogistikVarUrl() target="_blank" class="shipment" title=$oVersand->getIdentCode()}{lang key='packageTracking' section='order'}{/link}</p>
                                {/if}
                            {/foreach}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>

    {* Lieferschein Popups *}
    {foreach $Bestellung->oLieferschein_arr as $oLieferschein}
        {block name='order-details-delivery-note-popup'}
        <div id="popup{$oLieferschein->getLieferschein()}" class="d-none">
            <h1>{if $Bestellung->cStatus == BESTELLUNG_STATUS_TEILVERSANDT}{lang key='partialShipped' section='order'}{else}{lang key='shipped' section='order'}{/if}</h1>
            {card}
                <strong>{lang key='shippingOrder' section='order'}</strong>: {$oLieferschein->getLieferscheinNr()}<br />
                <strong>{lang key='shippedOn' section='login'}</strong>: {$oLieferschein->getErstellt()|date_format:"%d.%m.%Y %H:%M"}<br />
            {/card}

            {if $oLieferschein->getHinweis()|@count_characters > 0}
                {alert variant="info"}{$oLieferschein->getHinweis()}{/alert}
            {/if}
            {card}
                {foreach $oLieferschein->oVersand_arr as $oVersand}
                    {if $oVersand->getIdentCode()}
                        <p>{link href=$oVersand->getLogistikVarUrl() target="_blank" class="shipment" title=$oVersand->getIdentCode()}{lang key='packageTracking' section='order'}{/link}</p>
                    {/if}
                {/foreach}
            {/card}

            <table class="table table-striped table-bordered">
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
        {/block}
    {/foreach}
{/block}
{/if}

{if !empty($Bestellung->cKommentar|trim)}
    <div class="h3">{lang key='yourOrderComment' section='login'}</div>
    <p>{$Bestellung->cKommentar}</p>
{/if}
{if !empty($oTrustedShopsBewertenButton->cPicURL)}
    {link href=$oTrustedShopsBewertenButton->cURL target="_blank"}{image src=$oTrustedShopsBewertenButton->cPicURL}{/link}
{/if}
