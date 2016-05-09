{includeMailTemplate template=header type=html}

Sehr {if $Kunde->cAnrede == "w"}geehrte{else}geehrter{/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
Ihre Bestellung vom {$Bestellung->dErstelldatum_de} mit Bestellnummer {$Bestellung->cBestellNr} wurde heute an Sie versandt.<br>
{if $Bestellung->cTrackingURL}
    Mit nachfolgendem Link können Sie sich über den Status Ihrer Sendung informieren:<br>
    <br>
    Tracking-Link: <a href="{$Bestellung->cTrackingURL}">{$Bestellung->cTrackingURL}</a><br>
{/if}
<br>
Wir wünschen Ihnen viel Spaß mit der Ware und bedanken für Ihren Einkauf und Ihr Vertrauen.<br>
<br>
Mit freundlichem Gruß,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}