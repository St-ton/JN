{includeMailTemplate template=header type=plain}

{if !empty($Benachrichtigung->cNachname) || !empty($Benachrichtigung->cVorname)}
Hallo{if !empty($Benachrichtigung->cVorname)} {$Benachrichtigung->cVorname}{/if}
{if !empty($Benachrichtigung->cNachname)} {$Benachrichtigung->cNachname}{/if},
{else}
Sehr geehrte Kundin, sehr geehrter Kunde,
{/if}

wir freuen uns, Ihnen mitteilen zu dürfen, dass das Produkt {$Artikel->cName} ab sofort wieder bei uns erhältlich ist.

Über diesen Link kommen Sie direkt zum Produkt in unserem Onlineshop: {$ShopURL}/{$Artikel->cURL}.

Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}
