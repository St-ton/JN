{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='account-order-details'}
    {block name='account-order-details-script-location'}
        <script>
            if (top.location !== self.location) {
                top.location = self.location.href;
            }
        </script>
    {/block}
    {block name='account-order-details-heading'}
        <h1>{lang key='orderCompletedPre' section='checkout'}</h1>
    {/block}
    {block name='account-order-details-order-details-data'}
        {card no-body=true class='mb-3'}
            {cardheader}
                {row class='align-items-center'}
                {block name='account-order-details-order-heading'}
                    {col cols=12 lg=3 class='border-lg-right'}
                        <span class="font-weight-bold">
                            <span class="far fa-calendar mr-2"></span>{$Bestellung->dErstelldatum_de}
                        </span>
                    {/col}
                    {col cols=6 class='col-lg-auto'}
                        {lang key='yourOrderId' section='checkout'}: {$Bestellung->cBestellNr}
                    {/col}
                    {col cols=6 class='col-lg-auto text-right text-lg-left'}
                        {lang key='orderStatus' section='login'}: {$Bestellung->Status}
                    {/col}
                {/block}
                {/row}
            {/cardheader}
            {if isset($Kunde) && $Kunde->kKunde > 0}
                {cardbody}
                    {row}
                        {col cols=12 lg=3 class='border-lg-right'}
                            {block name='account-order-details-total'}
                                <span class="subheadline">{lang key='orderOverview' section='account data'}</span>
                                <ul class="list-unstyled mt-lg-5 border-bottom pb-3 pb-lg-5">
                                    <li class="mb-2">
                                        {lang key='subtotal' section='account data'}: <span class="float-right text-nowrap">{$Bestellung->WarensummeLocalized[1]}</span>
                                    </li>
                                    {if $Bestellung->GuthabenNutzen == 1}
                                    <li class="mb-2">
                                        {lang key='useCredit' section='account data'}: <span class="float-right text-nowrap">$Bestellung->GutscheinLocalized}</span>
                                    </li>
                                    {/if}
                                    <li class="font-weight-bold mb-2">
                                        {lang key='totalSum' section='global'} {if $NettoPreise} {lang key='gross' section='global'}{/if}:
                                        <span class="float-right text-nowrap">{$Bestellung->WarensummeLocalized[0]}</span>
                                    </li>
                                    {if $Einstellungen.global.global_steuerpos_anzeigen !== 'N'}
                                        {foreach $Bestellung->Steuerpositionen as $taxPosition}
                                            <li class="text-muted font-size-sm">
                                                {$taxPosition->cName} <span class="float-right text-nowrap">{$taxPosition->cPreisLocalized}</span>
                                            </li>
                                        {/foreach}
                                    {/if}
                                </ul>
                            {/block}
                            <ul class="list-unstyled mt-lg-5">
                                <li class="mb-4">
                                    {block name='account-order-details-payment'}
                                        {lang key='paymentOptions' section='global'}:
                                        <span class="text-muted d-block font-size-sm">
                                            <ul class="list-unstyled">
                                                <li>{$Bestellung->cZahlungsartName}</li>
                                                <li>
                                                    {if $Bestellung->cStatus != BESTELLUNG_STATUS_STORNO && $Bestellung->dBezahldatum_de !== '00.00.0000'}
                                                        {lang key='payedOn' section='login'} {$Bestellung->dBezahldatum_de}
                                                    {else}
                                                        {if ($Bestellung->cStatus == BESTELLUNG_STATUS_OFFEN || $Bestellung->cStatus == BESTELLUNG_STATUS_IN_BEARBEITUNG)
                                                        && (($Bestellung->Zahlungsart->cModulId !== 'za_ueberweisung_jtl'
                                                        && $Bestellung->Zahlungsart->cModulId !== 'za_nachnahme_jtl'
                                                        && $Bestellung->Zahlungsart->cModulId !== 'za_rechnung_jtl'
                                                        && $Bestellung->Zahlungsart->cModulId !== 'za_barzahlung_jtl')
                                                        && (isset($Bestellung->Zahlungsart->bPayAgain) && $Bestellung->Zahlungsart->bPayAgain))}
                                                            {link href="bestellab_again.php?kBestellung={$Bestellung->kBestellung}"}{lang key='payNow' section='global'}{/link}
                                                        {else}
                                                            {lang key='notPayedYet' section='login'}
                                                        {/if}
                                                    {/if}
                                                </li>
                                            </ul>
                                        </span>
                                    {/block}
                                </li>
                                <li class="mb-4">
                                    {block name='account-order-details-shipping'}
                                        {lang key='shippingOptions' section='global'}:
                                        <span class="text-muted d-block font-size-sm">
                                            <ul class="list-unstyled">
                                                <li>{$Bestellung->cVersandartName}</li>
                                                {if $Bestellung->cStatus == BESTELLUNG_STATUS_VERSANDT}
                                                    <li>{lang key='shippedOn' section='login'} {$Bestellung->dVersanddatum_de}</li>
                                                {elseif $Bestellung->cStatus == BESTELLUNG_STATUS_TEILVERSANDT}
                                                    <li>{$Bestellung->Status}</li>
                                                {else}
                                                    <li><span>{lang key='notShippedYet' section='login'}</span></li>
                                                    {if $Bestellung->cStatus != BESTELLUNG_STATUS_STORNO}
                                                        <li>
                                                            <span>{lang key='shippingTime' section='global'}: {if isset($cEstimatedDeliveryEx)}{$cEstimatedDeliveryEx}{else}{$Bestellung->cEstimatedDelivery}{/if}</span>
                                                        </li>
                                                    {/if}
                                                {/if}
                                            </ul>
                                        </span>
                                    {/block}
                                </li>
                                <li class="mb-4">
                                    {block name='account-order-details-billing-address'}
                                        {lang key='billingAdress' section='checkout'}:
                                        <span class="text-muted d-block font-size-sm">
                                            {block name='account-order-details-include-inc-billing-address'}
                                                {include file='checkout/inc_billing_address.tpl' orderDetail=true}
                                            {/block}
                                        </span>
                                    {/block}
                                </li>
                                <li class="mb-4">
                                    {block name='account-order-details-shipping-address'}
                                        {lang key='shippingAdress' section='checkout'}:
                                        <span class="text-muted d-block font-size-sm">
                                            {if !empty($Lieferadresse->kLieferadresse)}
                                                {block name='account-order-details-include-inc-delivery-address'}
                                                    {include file='checkout/inc_delivery_address.tpl' orderDetail=true}
                                                {/block}
                                            {else}
                                                {lang key='shippingAdressEqualBillingAdress' section='account data'}
                                            {/if}
                                        </span>
                                    {/block}
                                </li>
                            </ul>
                        {/col}
                        {col cols=12 lg=9}
                            <span class="subheadline">{lang key='basket'}</span>
                            {block name='account-order-details-include-order-item'}
                                {include file='account/order_item.tpl' tplscope='confirmation'}
                            {/block}
                        {/col}
                    {/row}
                {/cardbody}
            {else}
                {cardbody}
                    {block name='account-order-details-request-plz'}
                        {row}
                            {col sm=12 md=6}
                                {form method="post" id='request-plz' action="{get_static_route}" class="evo-validate label-slide"}
                                    {input type="hidden" name="uid" value="{$uid}"}
                                <p>{lang key='enter_plz_for_details' section='account data'}</p>
                                {formgroup
                                    label-for="postcode"
                                    label={lang key='plz' section='account data'}
                                }
                                    {input
                                        type="text"
                                        name="plz"
                                        value=""
                                        id="postcode"
                                        class="postcode_input"
                                        placeholder="{lang key='plz' section='account data'}"
                                        required=true
                                        autocomplete="billing postal-code"
                                    }
                                {/formgroup}
                                    {button type="submit" value="1" class="w-auto" variant="primary"}
                                        {lang key='view' section='global'}
                                    {/button}
                                {/form}
                            {/col}
                            {col cols=12 md=9}
                                <strong>{lang key='basket'}</strong>
                                {block name='account-order-details-include-order-item'}
                                    {include file='account/order_item.tpl' tplscope='confirmation'}
                                {/block}
                            {/col}
                        {/row}
                    {/block}
                {/cardbody}
            {/if}
        {/card}
    {/block}

    {if isset($Kunde) && $Kunde->kKunde > 0}
        {block name='account-order-details-include-downloads'}
            {include file='account/downloads.tpl'}
        {/block}
        {block name='account-order-details-include-uploads'}
            {include file='account/uploads.tpl'}
        {/block}

        {if $Bestellung->oLieferschein_arr|@count > 0}
            {block name='account-order-details-delivery-note-content'}
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
                            {block name='account-order-details-delivery-notes'}
                                {foreach $Bestellung->oLieferschein_arr as $oLieferschein}
                                    <tr>
                                        <td>{link data=["toggle"=>"modal", "target"=>"#shipping-order-{$oLieferschein->getLieferschein()}"] id=$oLieferschein->getLieferschein() href="#" title=$oLieferschein->getLieferscheinNr()}{$oLieferschein->getLieferscheinNr()}{/link}</td>
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
                            {/block}
                        </tbody>
                    </table>
                </div>

                {* Lieferschein Popups *}
                {foreach $Bestellung->oLieferschein_arr as $oLieferschein}
                    {block name='account-order-details-delivery-note-popup'}
                        {modal id="shipping-order-{$oLieferschein->getLieferschein()}"
                            title=(($Bestellung->cStatus == BESTELLUNG_STATUS_TEILVERSANDT) ? {lang key='partialShipped' section='order'} : {lang key='shipped' section='order'})
                            class="fade"
                            size="lg"}
                            <div class="mb-3">
                                <strong>{lang key='shippingOrder' section='order'}</strong>: {$oLieferschein->getLieferscheinNr()}<br />
                                <strong>{lang key='shippedOn' section='login'}</strong>: {$oLieferschein->getErstellt()|date_format:"%d.%m.%Y %H:%M"}<br />
                            </div>

                            {if $oLieferschein->getHinweis()|@count_characters > 0}
                                {alert variant="info" class="mb-3"}{$oLieferschein->getHinweis()}{/alert}
                            {/if}
                            <div class="mb-3">
                                {foreach $oLieferschein->oVersand_arr as $oVersand}
                                    {if $oVersand->getIdentCode()}
                                        <p>{link href=$oVersand->getLogistikVarUrl() target="_blank" class="shipment" title=$oVersand->getIdentCode()}{lang key='packageTracking' section='order'}{/link}</p>
                                    {/if}
                                {/foreach}
                            </div>

                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>{lang key="partialShippedPosition" section="order"}</th>
                                    <th>{lang key="partialShippedCount" section="order"}</th>
                                    <th>{lang key='productNo' section='global'}</th>
                                    <th>{lang key='product' section='global'}</th>
                                    <th>{lang key="order" section="global"}</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $oLieferschein->oLieferscheinPos_arr as $oLieferscheinpos}
                                    <tr>
                                        <td>{$oLieferscheinpos@iteration}</td>
                                        <td>{$oLieferscheinpos->getAnzahl()}</td>
                                        <td>{$oLieferscheinpos->oPosition->cArtNr}</td>
                                        <td>
                                            {$oLieferscheinpos->oPosition->cName}
                                            <ul class="list-unstyled text-muted small">
                                                {if !empty($oLieferscheinpos->oPosition->cHinweis)}
                                                    <li class="text-info notice">{$oLieferscheinpos->oPosition->cHinweis}</li>
                                                {/if}

                                                {* eindeutige Merkmale *}
                                                {if $oLieferscheinpos->oPosition->Artikel->cHersteller && $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen != "N"}
                                                    <li class="manufacturer">
                                                        <strong>{lang key='manufacturer' section='productDetails'}</strong>:
                                                        <span class="values">
                                                           {$oLieferscheinpos->oPosition->Artikel->cHersteller}
                                                        </span>
                                                    </li>
                                                {/if}

                                                {if $Einstellungen.kaufabwicklung.bestellvorgang_artikelmerkmale == 'Y' && !empty($oLieferscheinpos->oPosition->Artikel->oMerkmale_arr)}
                                                    {foreach $oLieferscheinpos->oPosition->Artikel->oMerkmale_arr as $oMerkmale_arr}
                                                        <li class="characteristic">
                                                            <strong>{$oMerkmale_arr->cName}</strong>:
                                                            <span class="values">
                                                                {foreach $oMerkmale_arr->oMerkmalWert_arr as $oWert}
                                                                    {if !$oWert@first}, {/if}
                                                                    {$oWert->cWert}
                                                                {/foreach}
                                                            </span>
                                                        </li>
                                                    {/foreach}
                                                {/if}
                                            </ul>
                                        </td>
                                        <td>{$Bestellung->cBestellNr}</td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        {/modal}
                    {/block}
                {/foreach}
            {/block}
        {/if}

        {block name='account-order-details-order-comment'}
            {if !empty($Bestellung->cKommentar|trim)}
                <div class="h3">{lang key='yourOrderComment' section='login'}</div>
                <p>{$Bestellung->cKommentar}</p>
            {/if}
        {/block}
        {block name='account-order-details-actions'}
            {row}
                {col md=3 cols=12}
                    {link class="btn btn-secondary btn-block" href="{get_static_route id='jtl.php'}?bestellungen=1"}
                        {lang key='back'}
                    {/link}
                {/col}
            {/row}
        {/block}
    {/if}
{/block}
