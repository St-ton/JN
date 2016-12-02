<p>Dear shop owner,</p>

<p>the customer {$oKunde->cVorname} {$oKunde->cNachname} has selected in the following checkboxoptions at {$cAnzeigeOrt}:</p>

<p>
	{assign var=kSprache value=$oSprache->kSprache}
	- {$oCheckBox->cName}, {$oCheckBox->oCheckBoxSprache_arr[$kSprache]->cText}
</p>
