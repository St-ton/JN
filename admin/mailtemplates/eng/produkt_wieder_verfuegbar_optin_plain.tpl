{includeMailTemplate template=header type=plain}

Dear Customer,

Please use the following confirmation-Link, which you can insert into your browser,
to get the information, if the article
"{$Artikel->cName}"
is available again: {$Optin->activationURL}

If you want to unsubscribe from this notification feature,
please follow the following link with your browser:
{$Optin->deactivationURL}

Yours sincerely,
{$Einstellungen.global.global_shopname}

{includeMailTemplate template=footer type=plain}
