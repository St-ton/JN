{includeMailTemplate template=header type=plain}

Hello {$Kunde->cVorname},

Please find attached a voucher worth {$Neukunde->fGuthaben} for {$Firma->cName}.

By the way, I'm inviting you as part of the customer referral programme by {$Firma->cName}.

Kind regards,
{$Bestandskunde->cVorname} {$Bestandskunde->cNachname}

{includeMailTemplate template=footer type=plain}
