{includeMailTemplate template=header type=plain}

Dear customer,

We're happy to inform you that our product {$Artikel->cName} is once again available in our online shop.

Link to product: {$ShopURL}/{$Artikel->cURL}

Yours sincerely,
{$Einstellungen.global.global_shopname}

{includeMailTemplate template=footer type=plain}
