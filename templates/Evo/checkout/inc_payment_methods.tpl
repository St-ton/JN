{if (int)$ShopCreditAmount > 0 && (int)$OrderAmount === 0}
    <div class="col-xs-12">
        <div class="checkbox">
            <label class="btn-block" for="using-shop-credit">
                <input type="checkbox" name="using-shop-credit" id="using-shop-credit"{if (int)$OrderAmount === 0} checked{/if}>
                <input type="hidden" name="Zahlungsart" value="{$AktiveZahlungsart}">
                <span style="text-transform:none; font-size:12pt;">
                    {if (int)$OrderAmount === 0}
                    <span class="">Guthaben verrechnet. Keine Zahlung erforderlich.</span>
                    {/if}
                </span>
            </label>
        </div>
    </div>
{else}
    {foreach $Zahlungsarten as $zahlungsart}
        <div id="{$zahlungsart->cModulId}" class="col-xs-12">
            <div class="radio">
                <label for="payment{$zahlungsart->kZahlungsart}" class="btn-block">
                    <input name="Zahlungsart" value="{$zahlungsart->kZahlungsart}" class="radio-checkbox" type="radio"
                           id="payment{$zahlungsart->kZahlungsart}"{if $AktiveZahlungsart === $zahlungsart->kZahlungsart || $Zahlungsarten|@count === 1} checked{/if}{if $zahlungsart@first} required{/if}>
                    <span class="control-label label-default">
                        {if $zahlungsart->cBild}
                            <img src="{$zahlungsart->cBild}" alt="{$zahlungsart->angezeigterName|trans}" class="img-responsive-width img-sm">
                        {else}
                            <span class="content">
                                <span class="title">{$zahlungsart->angezeigterName|trans}</span>
                            </span>
                        {/if}
                        {if $zahlungsart->fAufpreis != 0}
                            <span class="badge pull-right">
                            {if $zahlungsart->cGebuehrname|has_trans}
                                <span>{$zahlungsart->cGebuehrname|trans} </span>
                            {/if}
                                {$zahlungsart->cPreisLocalized}
                        </span>
                        {/if}
                        {if $zahlungsart->cHinweisText|has_trans}
                            <span class="btn-block">
                            <small>{$zahlungsart->cHinweisText|trans}</small>
                        </span>
                        {/if}
                    </span>
                </label>
            </div>
        </div>
    {/foreach}
{/if}
