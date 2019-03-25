{includeMailTemplate template=header type=html}

Guten Tag,<br><br>

wir freuen uns, Sie als Newsletter-Abonnent bei {$Firma->cName} begrüßen zu können.<br>
<br>
Bitte klicken Sie den folgenden Freischaltcode, um Newsletter zu empfangen:<br>
<a href="{$NewsletterEmpfaenger->cFreischaltURL}">{$NewsletterEmpfaenger->cFreischaltURL}</a><br>
<br>
Sie können sich jederzeit vom Newsletter abmelden indem Sie entweder den Löschcode <a href="{$NewsletterEmpfaenger->cLoeschURL}">{$NewsletterEmpfaenger->cLoeschURL}</a> eingeben oder den Link Newsletter im Shop besuchen.<br>
<br>
Mit freundlichem Gruß,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}