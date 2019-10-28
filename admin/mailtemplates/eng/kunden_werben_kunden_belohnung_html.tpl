{includeMailTemplate template=header type=html}

Dear {$Kunde->cVorname} {$Kunde->cNachname},<br>
<br>
As part of our customer referral programme, we are pleased to grant you a reward of {$BestandskundenBoni->fGuthaben}.
<br>
Thank you for taking part!
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}