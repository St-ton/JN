{includeMailTemplate template=header type=plain}

Guten Tag,

wir freuen uns, Ihnen mitteilen zu dürfen, dass das Produkt {$Artikel->cName} ab sofort wieder bei uns erhältlich ist.

Über diesen Link kommen Sie direkt zum Produkt in unserem Onlineshop: {$ShopURL}/{$Artikel->cURL}.

Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}
