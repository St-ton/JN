{block name='checkout-inc-order-completed'}
    {card id="order-confirmation"}
        {block name='checkout-inc-order-completed-alert'}
            <p class="mb-4 mb-md-5">{lang key='orderConfirmationPost' section='checkout'}</p>
        {/block}
        {block name='checkout-inc-order-completed-id-payment'}
            <ul class="list-unstyled mb-md-6">
                <li><strong>{lang key='yourOrderId' section='checkout'}:</strong> {$Bestellung->cBestellNr}</li>
                <li><strong>{lang key='yourChosenPaymentOption' section='checkout'}:</strong> {$Bestellung->cZahlungsartName}</li>
            </ul>
        {/block}
    {/card}
{/block}
