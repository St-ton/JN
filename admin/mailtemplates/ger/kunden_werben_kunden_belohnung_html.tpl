{includeMailTemplate template=header type=html}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
Sie erhalten im Rahmen der Aktion Kunden werben Kunden ein Guthaben von {$BestandskundenBoni->fGuthaben}.<br>
<br>
Wir bedanken uns für Ihre Teilnahme!<br>
<br>
Mit freundlichem Gruß,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}