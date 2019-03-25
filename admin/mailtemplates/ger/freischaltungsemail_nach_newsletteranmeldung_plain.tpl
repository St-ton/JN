{includeMailTemplate template=header type=plain}

Guten Tag,

wir freuen uns, Sie als Newsletter-Abonnent bei {$Firma->cName} begrüßen zu können.

Bitte klicken Sie den folgenden Freischaltcode, um Newsletter zu empfangen:
{$NewsletterEmpfaenger->cFreischaltURL}

Sie können sich jederzeit vom Newsletter abmelden indem Sie entweder den Löschcode <a href="{$NewsletterEmpfaenger->cLoeschURL}">{$NewsletterEmpfaenger->cLoeschURL}</a>} eingeben oder den Link Newsletter im Shop besuchen.

Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}