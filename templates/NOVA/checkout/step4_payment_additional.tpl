{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{form id="form_payment_extra" class="form payment_extra" method="post" action="{get_static_route id='bestellvorgang.php'}"}
    <div id="order-additional-payment" class="mb-3 form-group">
        {include file=$Zahlungsart->cZusatzschrittTemplate}
        {input type="hidden" name="zahlungsartwahl" value="1"}
        {input type="hidden" name="zahlungsartzusatzschritt" value="1"}
        {input type="hidden" name="Zahlungsart" value=$Zahlungsart->kZahlungsart}
    </div>
    <div class="text-right">
        {button type="submit" value="1" variant="primary" size="lg" class="submit_once"}
            {lang key='continueOrder' section='account data'}
        {/button}
    </div>
{/form}
