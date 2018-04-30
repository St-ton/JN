{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{if $Kunde->cFirma}
    {$Kunde->cFirma}
    <br />
{/if}
{if $Kunde->cZusatz}
    {$Kunde->cZusatz}
    <br />
{/if}
{if $Kunde->cAnrede === 'w'}{lang key='salutationW'}{elseif $Kunde->cAnrede === 'm'}{lang key='salutationM'}{/if} {$Kunde->cTitel} {$Kunde->cVorname} {$Kunde->cNachname}
<br />{$Kunde->cStrasse} {$Kunde->cHausnummer}<br />{if $Kunde->cAdressZusatz}{$Kunde->cAdressZusatz}<br />{/if}
{$Kunde->cPLZ} {$Kunde->cOrt}<br />{if $Kunde->cBundesland}{$Kunde->cBundesland}<br />{/if}
{if $Kunde->angezeigtesLand}{$Kunde->angezeigtesLand}{else}{$Kunde->cLand}{/if}<br />
<br />{if $Kunde->cUSTID}{lang key="ustid" section="account data"}: {$Kunde->cUSTID}<br />{/if}
{if $Kunde->cTel}{lang key="tel" section="account data"}: {$Kunde->cTel}<br />{/if}
{if $Kunde->cFax}{lang key="fax" section="account data"}: {$Kunde->cFax}<br />{/if}
{if $Kunde->cMobil}{lang key="mobile" section="account data"}: {$Kunde->cMobil}<br />{/if}
{$Kunde->cMail}
