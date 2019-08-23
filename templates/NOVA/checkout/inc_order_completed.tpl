{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-inc-order-completed'}
    {card id="order-confirmation"}
        {block name='checkout-inc-order-completed-alert'}
            <p>{lang key='orderConfirmationPost' section='checkout'}</p>
        {/block}
        {block name='checkout-inc-order-completed-id-payment'}
            <p><span class="font-weight-bold">{lang key='yourOrderId' section='checkout'}:</span> {$Bestellung->cBestellNr}</p>
            <p><span class="font-weight-bold">{lang key='yourChosenPaymentOption' section='checkout'}:</span> {$Bestellung->cZahlungsartName}</p>
        {/block}
    {/card}
{/block}
