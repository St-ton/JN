<p>Sehr geehrter Shopbetreiber,</p>

<p>der Kunde {$oKunde->cVorname} {$oKunde->cNachname} hat im Bereich {$cAnzeigeOrt} folgende Checkboxoption gewählt:</p>

<p>
	{assign var=kSprache value=$oSprache->kSprache}
	- {$oCheckBox->cName}, {$oCheckBox->oCheckBoxSprache_arr[$kSprache]->cText}
</p>
