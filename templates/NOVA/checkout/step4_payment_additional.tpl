{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-step4-payment-additional'}
    {form id="form_payment_extra" class="form payment_extra label-slide" method="post" action="{get_static_route id='bestellvorgang.php'}"}
        {block name='checkout-step4-payment-additional-form-content'}
            <div id="order-additional-payment" class="mb-3 form-group">
                {block name='checkout-step4-payment-include-additional-steps'}
                    {include file=$Zahlungsart->cZusatzschrittTemplate}
                {/block}
                {input type="hidden" name="zahlungsartwahl" value="1"}
                {input type="hidden" name="zahlungsartzusatzschritt" value="1"}
                {input type="hidden" name="Zahlungsart" value=$Zahlungsart->kZahlungsart}
            </div>
            {block name='checkout-step4-payment-include-form-submit'}
                {row class='mt-5'}
                    {col cols=12 md=4 class='order-1 order-md-2'}
                        {button type="submit" value="1" variant="primary" block=true class="submit_once mb-3"}
                            {lang key='continueOrder' section='account data'}
                        {/button}
                    {/col}
                    {col cols=12 md=4 class='order-2 order-md-1'}
                        {button block=true type="link" href="{get_static_route id='bestellvorgang.php'}?editVersandart=1" variant="outline-primary"}
                            {lang key='back'}
                        {/button}
                    {/col}
                {/row}
            {/block}
        {/block}
    {/form}
{/block}
