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

{row class="mb-3"}
    {col}
        {block name='order-details-order-info'}
        {listgroup}
            {listgroupitem}<strong>{lang key='yourOrderId' section='checkout'}:</strong> {$Bestellung->cBestellNr}{/listgroupitem}
            {listgroupitem}<strong>{lang key='orderDate' section='login'}:</strong> {$Bestellung->dErstelldatum_de}{/listgroupitem}
            {listgroupitem variant="info"}<strong>Status:</strong> {$Bestellung->Status}{/listgroupitem}
        {/listgroup}
        {/block}
    {/col}
{/row}

{row class="mb-3"}
    {col md=6}
        {block name='order-details-billing-address'}
        {card}
            <div class="h3">{block name='order-details-billing-address-title'}{lang key='billingAdress' section='checkout'}{/block}</div>
            {include file='checkout/inc_billing_address.tpl' Kunde=$billingAddress}
        {/card}
        {/block}
    {/col}
    {col md=6}
        {block name='order-details-shipping-address'}
        {card}
            {if !empty($Lieferadresse->kLieferadresse)}
                <div class="h3">{block name='order-details-shipping-address-title'}{lang key='shippingAdress' section='checkout'}{/block}</div>
                {include file='checkout/inc_delivery_address.tpl'}
            {else}
                <div class="h3">{block name='order-details-shipping-address-title'}{lang key='shippingAdressEqualBillingAdress' section='account data'}{/block}</div>
                {include file='checkout/inc_billing_address.tpl' Kunde=$billingAddress}
            {/if}
        {/card}
        {/block}
    {/col}
{/row}
{row class="mb-3"}
    {col md=6}
        {card}
            {block name='order-details-payment'}
            <div class="h3">{block name='order-details-payment-title'}{lang key='paymentOptions' section='global'}: {$Bestellung->cZahlungsartName}{/block}</div>
            {block name='order-details-payment-body'}
            {if $Bestellung->cStatus != BESTELLUNG_STATUS_STORNO && $Bestellung->dBezahldatum_de !== '00.00.0000'}
                {lang key='payedOn' section='login'} {$Bestellung->dBezahldatum_de}
            {else}
                {if ($Bestellung->cStatus == BESTELLUNG_STATUS_OFFEN || $Bestellung->cStatus == BESTELLUNG_STATUS_IN_BEARBEITUNG) && (($Bestellung->Zahlungsart->cModulId !== 'za_ueberweisung_jtl' && $Bestellung->Zahlungsart->cModulId !== 'za_nachnahme_jtl' && $Bestellung->Zahlungsart->cModulId !== 'za_rechnung_jtl' && $Bestellung->Zahlungsart->cModulId !== 'za_barzahlung_jtl' && $Bestellung->Zahlungsart->cModulId !== 'za_billpay_jtl') && (isset($Bestellung->Zahlungsart->bPayAgain) && $Bestellung->Zahlungsart->bPayAgain))}
                    {link href="bestellab_again.php?kBestellung={$Bestellung->kBestellung}"}{lang key='payNow' section='global'}{/link}
                {else}
                    {lang key='notPayedYet' section='login'}
                {/if}
            {/if}
            {/block}
            {/block}
        {/card}
    {/col}
    {col md=6}
        {card}
            {block name='order-details-shipping'}
            <div class="h3">{block name='order-details-shipping-title'}{lang key='shippingOptions' section='global'}: {$Bestellung->cVersandartName}{/block}</div>
            {cardbody}
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
            {/cardbody}
            {/block}
        {/card}
    {/col}
{/row}

{block name='order-details-basket'}
<div class="h2">{lang key='basket'}</div>

<div class="table-responsive">
    {include file='account/order_item.tpl' tplscope='confirmation'}
</div>

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
