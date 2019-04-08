{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-inc-order-completed'}
    {card id="order-confirmation"}
        {block name='checkout-inc-order-completed-alert'}
            {alert variant="info"}{lang key='orderConfirmationPost' section='checkout'}{/alert}
        {/block}
        {block name='checkout-inc-order-completed-id-payment'}
            <p>{lang key='yourOrderId' section='checkout'}: {$Bestellung->cBestellNr}</p>
            <p>{lang key='yourChosenPaymentOption' section='checkout'}: {$Bestellung->cZahlungsartName}</p>
        {/block}
    {/card}
{/block}
