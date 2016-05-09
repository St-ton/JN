{includeMailTemplate template=header type=plain}

Sehr {if $Kunde->cAnrede == "w"}geehrte{else}geehrter{/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

der Versandstatus Ihrer Bestellung mit der Bestell-Nr. {$Bestellung->cBestellNr} hat sich geändert.

{foreach name=pos from=$Bestellung->oLieferschein_arr item=oLieferschein}
{if !$oLieferschein->getEmailVerschickt()}
{foreach from=$oLieferschein->oPosition_arr item=Position}
{$Position->nAusgeliefert} x {if $Position->nPosTyp==1}{$Position->cName} {if $Position->cArtNr}({$Position->cArtNr}){/if}
{foreach name=variationen from=$Position->WarenkorbPosEigenschaftArr item=WKPosEigenschaft}
{$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}
{/foreach}
{if $Position->cSeriennummer|@count_characters > 0}
Seriennummer: {$Position->cSeriennummer}
{/if}
{if $Position->dMHD|@count_characters > 0}
Mindesthaltbarkeitsdatum: {$Position->dMHD_de}
{/if}
{if $Position->cChargeNr|@count_characters > 0}
Charge: {$Position->cChargeNr}
{/if}
{else}
{$Position->cName}
{/if}
{/foreach}

{foreach from=$oLieferschein->oVersand_arr item=oVersand}
{if $oVersand->getIdentCode()|@count_characters > 0}
Tracking-Url: {$oVersand->getLogistikVarUrl()}
{/if}
{/foreach}
{/if}
{/foreach}

Über den weiteren Verlauf Ihrer Bestellung werden wir Sie jeweils gesondert informieren.

Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}