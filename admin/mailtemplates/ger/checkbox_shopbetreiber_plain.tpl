Sehr geehrter Shopbetreiber,

der Kunde {$oKunde->cVorname} {$oKunde->cNachname} hat im Bereich {$cAnzeigeOrt} folgende Checkboxoption gewählt:

{assign var=kSprache value=$oSprache->kSprache}
- {$oCheckBox->cName}, {$oCheckBox->oCheckBoxSprache_arr[$kSprache]->cText}
