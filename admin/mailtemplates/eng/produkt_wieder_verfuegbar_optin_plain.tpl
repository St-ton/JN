{includeMailTemplate template=header type=plain}

{if empty($Benachrichtigung->cVorname) && empty($Benachrichtigung->cNachname)}
Dear Customer,
{else}
Dear{if !empty($Benachrichtigung->cVorname)} {$Benachrichtigung->cVorname}{/if}
{if !empty($Benachrichtigung->cNachname)} {$Benachrichtigung->cNachname}{/if},
{/if}

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
