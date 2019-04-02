{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{card id="order-confirmation"}
    {block name='checkout-order-confirmation'}
        {alert variant="info"}{lang key='orderConfirmationPost' section='checkout'}{/alert}
        <p>{lang key='yourOrderId' section='checkout'}: {$Bestellung->cBestellNr}</p>
        <p>{lang key='yourChosenPaymentOption' section='checkout'}: {$Bestellung->cZahlungsartName}</p>
    {/block}
{/card}
