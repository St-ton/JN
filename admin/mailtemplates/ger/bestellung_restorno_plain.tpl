{includeMailTemplate template=header type=plain}

Sehr geehrter Kunde,

Ihre Bestellung bei {$Einstellungen.global.global_shopname} wurde soeben reaktivert.
Bestellnummer: {$Bestellung->cBestellNr}

Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}