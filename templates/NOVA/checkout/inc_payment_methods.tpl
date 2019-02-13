{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{radiogroup}
    {foreach $Zahlungsarten as $zahlungsart}
        {col cols=12 id="{$zahlungsart->cModulId}"}
            {radio name="Zahlungsart"
                    value="{$zahlungsart->kZahlungsart}"
                    id="payment{$zahlungsart->kZahlungsart}"
                    checked=($AktiveZahlungsart === $zahlungsart->kZahlungsart || $Zahlungsarten|@count === 1)
                    required=($zahlungsart@first)
            }
                {if $zahlungsart->cBild}
                    {image src="{$zahlungsart->cBild}" alt="{$zahlungsart->angezeigterName|trans}" class="img-fluid img-sm"}
                {else}
                    <span class="content">
                        <span class="title">{$zahlungsart->angezeigterName|trans}</span>
                    </span>
                {/if}
                {if $zahlungsart->fAufpreis != 0}
                    <span class="badge badge-pill badge-primary float-right">
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
            {/radio}
        {/col}
    {/foreach}
{/radiogroup}
