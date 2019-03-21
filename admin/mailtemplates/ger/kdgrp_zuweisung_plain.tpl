{includeMailTemplate template=header type=plain}

Guten Tag {$Kunde->cVorname} {$Kunde->cNachname},

wir haben Ihre Kundengruppe geändert. Sie müßten ab sofort andere Preise als den Standardpreis angezeigt bekommen.

Momentan haben wir es noch nicht geschafft, alle Preise anzupassen.

Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}