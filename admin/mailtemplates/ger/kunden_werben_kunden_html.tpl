{includeMailTemplate template=header type=html}

Hallo {$Kunde->cVorname},<br><br>

anbei bekommst du ein Guthaben von {$Neukunde->fGuthaben} für {$Firma->cName}.<br><br>

Übrigens, ich werbe Dich im Rahmen der Aktion Kunden werben Kunden von {$Firma->cName}.<br><br>

Viele Grüße<br>
{$Bestandskunde->cVorname} {$Bestandskunde->cNachname}

{includeMailTemplate template=footer type=html}