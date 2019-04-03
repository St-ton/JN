{includeMailTemplate template=header type=plain}

{if !empty($Benachrichtigung->cNachname) || !empty($Benachrichtigung->cVorname)}
Dear{if !empty($Benachrichtigung->cVorname)} {$Benachrichtigung->cVorname}{/if}
{if !empty($Benachrichtigung->cNachname)} {$Benachrichtigung->cNachname}{/if},
{else}
Dear customer,
{/if}

We're happy to inform you that our product {$Artikel->cName} is once again available in our online shop.

Link to product: {$ShopURL}/{$Artikel->cURL}

Yours sincerely,
{$Einstellungen.global.global_shopname}

{includeMailTemplate template=footer type=plain}
