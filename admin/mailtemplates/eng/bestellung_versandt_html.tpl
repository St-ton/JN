{includeMailTemplate template=header type=html}

Dear {if $Kunde->cAnrede == "w"}geehrte{else}geehrter{/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
Your order dated {$Bestellung->dErstelldatum_de} mit Bestellnummer {$Bestellung->cBestellNr} has been shipped to you today.<br>

{if $Bestellung->cTrackingURL}
    You can track the status of your shipment via the following link:
    <br>
    {$Bestellung->cTrackingURL}
{/if}
<br>
We hope the merchandise meets with your full satisfaction and thank you for your purchase.
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}