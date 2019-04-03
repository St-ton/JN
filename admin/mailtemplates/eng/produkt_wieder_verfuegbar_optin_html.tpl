{includeMailTemplate template=header type=html}

{if empty($Benachrichtigung->cVorname) && empty($Benachrichtigung->cNachname)}
Dear Customer,<br>
{else}
Dear{if !empty($Benachrichtigung->cVorname)} {$Benachrichtigung->cVorname}{/if}
{if !empty($Benachrichtigung->cNachname)} {$Benachrichtigung->cNachname}{/if},<br>
{/if}
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
