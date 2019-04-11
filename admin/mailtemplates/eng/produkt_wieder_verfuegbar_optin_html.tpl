{includeMailTemplate template=header type=html}

Dear Customer,<br>
<br>
Please use the following confirmation-Link<br>
<a href="{$Optin->activationURL}">{$Optin->activationURL}</a>,<br>
to get the information, if the article
<b>{$Artikel->cName}</b><br>
is available again.<br>
<br>
If you want to unsubscribe from this notification feature,<br>
please click the following link:<br>
<a href="{$Optin->deactivationURL}">{$Optin->deactivationURL}<a><br>
<br>
<br>
Yours sincerely,<br>
{$Einstellungen.global.global_shopname}

{includeMailTemplate template=footer type=html}
