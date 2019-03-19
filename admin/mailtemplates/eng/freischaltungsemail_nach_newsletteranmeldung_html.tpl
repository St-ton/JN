{includeMailTemplate template=header type=html}

We're pleased to welcome you as a new newsletter subscriber at {$Firma->cName}.<br>
<br>
Please click the activation code below to receive your newsletter:<br>
<a href="{$NewsletterEmpfaenger->cFreischaltURL}">{$NewsletterEmpfaenger->cFreischaltURL}</a><br>

You can unsubscribe the newsletter at any time either by entering the unsubscribe code <a href="{$NewsletterEmpfaenger->cLoeschURL}">{$NewsletterEmpfaenger->cLoeschURL}</a>} or clicking the Newsletter link in the shop.
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}