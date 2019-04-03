{includeMailTemplate template=header type=html}

{if !empty($Benachrichtigung->cNachname) || !empty($Benachrichtigung->cVorname)}
Hallo{if !empty($Benachrichtigung->cVorname)} {$Benachrichtigung->cVorname}{/if}
{if !empty($Benachrichtigung->cNachname)} {$Benachrichtigung->cNachname}{/if},<br>
{else}
Sehr geehrte Kundin, sehr geehrter Kunde,<br>
{/if}
<br>
wir freuen uns, Ihnen mitteilen zu dürfen, dass das Produkt {$Artikel->cName} ab sofort wieder bei uns erhältlich ist.<br>
<br>
Über diesen Link kommen Sie direkt zum Produkt in unserem Onlineshop: <a href="{$ShopURL}/{$Artikel->cURL}">{$Artikel->cName}</a><br>
<br>
Mit freundlichem Gruß,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}
