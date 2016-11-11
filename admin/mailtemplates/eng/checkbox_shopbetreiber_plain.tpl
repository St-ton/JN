Dear shop owner,

the customer {$oKunde->cVorname} {$oKunde->cNachname} has selected in the following checkboxoptions at {$cAnzeigeOrt}:

{assign var=kSprache value=$oSprache->kSprache}
- {$oCheckBox->cName}, {$oCheckBox->oCheckBoxSprache_arr[$kSprache]->cText}
