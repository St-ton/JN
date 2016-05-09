{includeMailTemplate template=header type=plain}

Dear {if $Kunde->cAnrede == "w"}geehrte{else}geehrter{/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

As part of our customer recommendation program, we are pleased to grant you a reward of {$BestandskundenBoni->fGuthaben}.

Thank you for taking part!

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}