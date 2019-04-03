{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='account-retrospective-payment'}
    {form id="form-payment-extra" class="form payment-extra" method="post" action="bestellab_again.php"}
        <fieldset class="outer">
            {block name='account-retrospective-payment-include-additional-step'}
                {include file=$Bestellung->Zahlungsart->cZusatzschrittTemplate}
            {/block}
            {block name='account-retrospective-payment-form-submit'}
                {input type="hidden" name="zusatzschritt" value="1"}
                {input type="hidden" name="kBestellung" value=$Bestellung->kBestellung}
                <p class="box_plain">
                    {button type="submit" value="1"}
                        {lang key='completeOrder' section='shipping payment'}
                    {/button}
                </p>
            {/block}
        </fieldset>
    {/form}
{/block}
