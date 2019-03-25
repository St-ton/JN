{includeMailTemplate template=header type=plain}

Hello,

we're pleased to welcome you as a new newsletter subscriber at {$Firma->cName}.

Please click the activation code below to receive your newsletter:
{$NewsletterEmpfaenger->cFreischaltURL}

You can unsubscribe the newsletter at any time either by entering the unsubscribe code <a href="{$NewsletterEmpfaenger->cLoeschURL}">{$NewsletterEmpfaenger->cLoeschURL}</a>} or clicking the Newsletter link in the shop.

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}