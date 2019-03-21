{includeMailTemplate template=header type=html}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},
<br>
wir haben Ihre Kundengruppe geändert. Sie müßten ab sofort andere Preise als den Standardpreis angezeigt bekommen.<br>
<br>
Momentan haben wir es noch nicht geschafft, alle Preise anzupassen.<br>
<br>
Mit freundlichem Gruß,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}