{includeMailTemplate template=header type=plain}

Hallo {$Kunde->cVorname},

anbei bekommst du ein Guthaben von {$Neukunde->fGuthaben} für {$Firma->cName}.

Übrigens, ich werbe dich im Rahmen der Aktion Kunden werben Kunden von {$Firma->cName}.

Viele Grüße
{$Bestandskunde->cVorname} {$Bestandskunde->cNachname}

{includeMailTemplate template=footer type=plain}
