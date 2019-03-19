{includeMailTemplate template=header type=html}

Dear customer,<br>
<br>
Your order at {$Einstellungen.global.global_shopname} has been reactivated.<br>
<strong>Order number:</strong> {$Bestellung->cBestellNr}<br>
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}