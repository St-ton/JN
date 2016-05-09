{includeMailTemplate template=header type=plain}

Sehr {if $Kunde->cAnrede == "w"}geehrte{else}geehrter{/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

Ihre Bestellung vom {$Bestellung->dErstelldatum_de} mit Bestellnummer {$Bestellung->cBestellNr} wurde heute an Sie versandt. 

{if $Bestellung->cTrackingURL}
Mit nachfolgendem Link können Sie sich über den Status Ihrer Sendung informieren: 

{$Bestellung->cTrackingURL}
{/if}

Wir wünschen Ihnen viel Spaß mit der Ware und bedanken für Ihren Einkauf und Ihr Vertrauen.

Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}