{includeMailTemplate template=header type=plain}

Dear {$Kunde->cVorname} {$Kunde->cNachname},

As part of our customer referral programme, we are pleased to grant you a reward of {$BestandskundenBoni->fGuthaben}.

Thank you for taking part!

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}