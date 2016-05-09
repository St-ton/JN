{includeMailTemplate template=header type=html}

Dear {if $Kunde->cAnrede == "w"}geehrte{else}geehrter{/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
As part of our customer recommendation program, we are pleased to grant you a reward of {$BestandskundenBoni->fGuthaben}.
<br>
Thank you for taking part!
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}