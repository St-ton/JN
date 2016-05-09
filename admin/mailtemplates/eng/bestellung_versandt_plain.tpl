{includeMailTemplate template=header type=plain}

Dear {if $Kunde->cAnrede == "w"}geehrte{else}geehrter{/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

Your order dated {$Bestellung->dErstelldatum_de} mit Bestellnummer {$Bestellung->cBestellNr} has been shipped to you today.

{if $Bestellung->cTrackingURL}
    You can track the status of your shipment via the following link:

    {$Bestellung->cTrackingURL}
{/if}

We hope the merchandise meets with your full satisfaction and thank you for your purchase.

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}