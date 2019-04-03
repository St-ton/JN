{includeMailTemplate template=header type=html}

{if isset($Kunde->kKunde) && $Kunde->kKunde > 0}
    Sehr {if $Kunde->cAnrede=="w"}geehrte Frau{else}geehrter Herr{/if} {$Kunde->cNachname},<br>
    <br>
{elseif isset($Receiver->cNachname) && $Receiver->cNachname !== ''}
    Sehr {if $Receiver->cAnrede=="w"}geehrte Frau{else}geehrter Herr{/if} {$Receiver->cNachname},<br>
    <br>
{else}
    Sehr geeherte Kundin, sehr geehrter Kunde,<br>
{/if}
<br>
Bitte klicken Sie den folgenden Freischalt-Link<br>
<a href="{$Optin->activationURL}">{$Optin->activationURL}</a>,<br>
<br>
um von uns informiert zu werden, sobald der Artikel<br>
<b>{$Artikel->cName}</b><br>
wieder verfügbar ist.<br>
<br>
Wenn Sie sich von dieser Benachrichtigungsfunktion abmelden möchten,<br>
klicken Sie bitte den folgenden Link an:<br>
<a href="{$Optin->deactivationURL}">{$Optin->deactivationURL}</a>,<br>
<br>
<br>
Mit freundlichem Gruß,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}
