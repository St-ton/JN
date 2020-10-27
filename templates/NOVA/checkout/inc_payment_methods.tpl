{block name='checkout-inc-payment-methods'}
    {radiogroup}
        {foreach $Zahlungsarten as $zahlungsart}
            {col cols=12 id=$zahlungsart->cModulId class="mb-3"}
                {radio name="Zahlungsart"
                        value=$zahlungsart->kZahlungsart
                        id="payment{$zahlungsart->kZahlungsart}"
                        checked=($AktiveZahlungsart === $zahlungsart->kZahlungsart || $Zahlungsarten|@count === 1)
                        required=($zahlungsart@first)
                }
                    {block name='checkout-inc-payment-methods-image-title'}
                        {if $zahlungsart->cBild}
                                {image src=$zahlungsart->cBild alt=$zahlungsart->angezeigterName|trans fluid=true class="img-sm"}
                        {else}
                            <span class="content">
                                <span class="title">{$zahlungsart->angezeigterName|trans}</span>
                            </span>
                        {/if}
                    {/block}
                    {if $zahlungsart->fAufpreis != 0}
                        {block name='checkout-inc-payment-methods-badge'}
                            <strong class="ml-3 float-right">
                            {if $zahlungsart->cGebuehrname|has_trans}
                                <span>{$zahlungsart->cGebuehrname|trans} </span>
                            {/if}
                                {$zahlungsart->cPreisLocalized}
                            </strong>
                        {/block}
                    {/if}
                    {if $zahlungsart->cHinweisText|has_trans}
                        {block name='checkout-inc-payment-methods-note'}
                            <span class="btn-block">
                                <small>{$zahlungsart->cHinweisText|trans}</small>
                            </span>
                        {/block}
                    {/if}
                {/radio}
            {/col}
        {/foreach}
    {/radiogroup}
{/block}
