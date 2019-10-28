{includeMailTemplate template=header type=html}

Hello {$Kunde->cVorname},<br><br>

Please find attached a voucher worth {$Neukunde->fGuthaben} for {$Firma->cName}.<br><br>

By the way, I'm inviting you as part of the customer referral programme by {$Firma->cName}.<br><br>

Kind regards,<br>
{$Bestandskunde->cVorname} {$Bestandskunde->cNachname}

{includeMailTemplate template=footer type=html}