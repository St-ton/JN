{includeMailTemplate template=header type=plain}

{if isset($Kunde->kKunde) && $Kunde->kKunde > 0}
    Sehr {if $Kunde->cAnrede=="w"}geehrte Frau{else}geehrter Herr{/if} {$Kunde->cNachname},
{elseif isset($Receiver->cNachname)}
    Sehr {if $Receiver->cAnrede=="w"}geehrte Frau{else}geehrter Herr{/if} {$Receiver->cNachname},
{else}
    Sehr geeherte Kundin, sehr geehrter Kunde,
{/if}
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
