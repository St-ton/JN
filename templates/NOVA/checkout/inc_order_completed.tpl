{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-inc-order-completed'}
    {card id="order-confirmation"}
        {block name='checkout-inc-order-completed-alert'}
            <p class="mb-4 mb-md-5">{lang key='orderConfirmationPost' section='checkout'}</p>
        {/block}
        {block name='checkout-inc-order-completed-id-payment'}
            <ul class="list-unstyled mb-md-6">
                <li><span class="font-weight-bold">{lang key='yourOrderId' section='checkout'}:</span> {$Bestellung->cBestellNr}</li>
                <li><span class="font-weight-bold">{lang key='yourChosenPaymentOption' section='checkout'}:</span> {$Bestellung->cZahlungsartName}</li>
            </ul>
        {/block}
    {/card}
{/block}
