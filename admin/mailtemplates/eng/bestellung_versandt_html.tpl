{includeMailTemplate template=header type=html}

Dear {if $Kunde->cAnrede == "w"}geehrte{else}geehrter{/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
Your order dated {$Bestellung->dErstelldatum_de} mit Bestellnummer {$Bestellung->cBestellNr} has been shipped to you today.<br>
<br>
{foreach name=pos from=$Bestellung->oLieferschein_arr item=oLieferschein}
    {if $oLieferschein->oVersand_arr|count > 1}
        You may track the shipping status by clicking on the links below:
    {else}
        You may track the shipping status by clicking on the link below:
    {/if}<br>
    <br>
    {foreach from=$oLieferschein->oVersand_arr item=oVersand}
        {if $oVersand->getIdentCode()|@count_characters > 0}
            <strong>Tracking URL:</strong> <a href="{$oVersand->getLogistikVarUrl()}">{$oVersand->getIdentCode()}</a><br>
            {if $oVersand->getHinweis()|@count_characters > 0}
                <strong>Tracking notice:</strong> {$oVersand->getHinweis()}<br>
            {/if}
        {/if}
    {/foreach}
{/foreach}
<br>
We hope the merchandise meets with your full satisfaction and thank you for your purchase.
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}