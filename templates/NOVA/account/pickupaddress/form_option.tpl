{if isset($pkAddresses)}
    {if !isset($selectedID)}
        {assign var=selectedID value=0}
    {/if}
    <option value="-1">{lang key='newPickupAddress' section='rma'}</option>
    {foreach $pkAddresses as $pkAddress}
        <option value="{$pkAddress->kLieferadresse}"{if
                ($pkAddress->nIstStandardLieferadresse == 1 && $selectedID === 0)
                || $selectedID == $pkAddress->kLieferadresse
                } selected{/if}>
            {if $pkAddress->cFirma}{$pkAddress->cFirma}, {/if}
            {$pkAddress->cStrasse} {$pkAddress->cHausnummer},
            {$pkAddress->cPLZ} {$pkAddress->cOrt}
        </option>
    {/foreach}
{/if}
