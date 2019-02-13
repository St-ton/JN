{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $Kunde->fGuthaben > 0 && (!isset($smarty.session.Bestellung->GuthabenNutzen) || !$smarty.session.Bestellung->GuthabenNutzen)}
    {form method="post" action="{get_static_route id='bestellvorgang.php'}" class="form form-inline"}
        {$jtl_token}
        <fieldset>
            {row}
                {col cols=6}
                    <p class="credit-description">{lang key='creditDesc' section='account data'}</p>
                {/col}
                {col cols=6}
                    {alert variant="info" class="credit-amount-description text-center"}
                        {lang key='yourCreditIs' section='account data'} <strong class="credit-amount">{$GuthabenLocalized}</strong>
                    {/alert}
                    {input type="hidden" name="guthabenVerrechnen" value="1"}
                    {input type="hidden" name="guthaben" value="1"}
                    {input type="submit" value="{lang key='useCredits' section='checkout'}" class="submit btn btn-secondary btn-block"}
                {/col}
            {/row}
        </fieldset>
    {/form}
{/if}
