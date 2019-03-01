{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{form id="form-payment-extra" class="form payment-extra" method="post" action="bestellab_again.php"}
    <fieldset class="outer">
        {input type="hidden" name="zusatzschritt" value="1"}
        {input type="hidden" name="kBestellung" value=$Bestellung->kBestellung}

        {include file=$Bestellung->Zahlungsart->cZusatzschrittTemplate}

        <p class="box_plain">
            {button type="submit" value="1"}
                {lang key='completeOrder' section='shipping payment'}
            {/button}
        </p>
    </fieldset>
{/form}
