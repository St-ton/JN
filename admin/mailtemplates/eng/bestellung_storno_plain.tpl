{includeMailTemplate template=header type=plain}

Dear customer,

Your order at {$Einstellungen.global.global_shopname} has been cancelled.
Order number: {$Bestellung->cBestellNr}

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}