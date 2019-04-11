{includeMailTemplate template=header type=plain}

Guten Tag,

Bitte nutzen Sie den folgenden Freischalt-Link
{$Optin->activationURL}
den Sie in Ihren Browser einfügen können, um von uns informiert zu werden, sobald
"{$Artikel->cName}" wieder verfügbar ist.

Wenn Sie sich von dieser Benachrichtigungsfunktion abmelden möchten,
folgen Sie bitte dem folgenden Link mit Ihrem Browser an:
{$Optin->deactivationURL}


Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}
