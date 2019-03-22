{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset($orderDetail)}
    {if $Lieferadresse->cFirma}
        {$Lieferadresse->cFirma}
        <br />
    {/if}
    {if $Lieferadresse->cZusatz}
        {$Lieferadresse->cZusatz}
        <br />
    {/if}
    {$Lieferadresse->cTitel} {$Lieferadresse->cVorname} {$Lieferadresse->cNachname}
    <br />{$Lieferadresse->cStrasse} {$Lieferadresse->cHausnummer}, {if $Lieferadresse->cAdressZusatz}{$Lieferadresse->cAdressZusatz},{/if}
    {$Lieferadresse->cPLZ} {$Lieferadresse->cOrt},
    {if $Lieferadresse->cLand}{$Lieferadresse->cLand}<br />{/if}
{else}
    {if $Lieferadresse->cFirma}
        {$Lieferadresse->cFirma}
        <br />
    {/if}
    {if $Lieferadresse->cZusatz}
        {$Lieferadresse->cZusatz}
        <br />
    {/if}
    {if $Lieferadresse->cAnrede === 'w'}{lang key='salutationW'}{elseif $Lieferadresse->cAnrede === 'm'}{lang key='salutationM'}{/if} {$Lieferadresse->cTitel} {$Lieferadresse->cVorname} {$Lieferadresse->cNachname}
    <br />{$Lieferadresse->cStrasse} {$Lieferadresse->cHausnummer}
    <br />{if $Lieferadresse->cAdressZusatz}{$Lieferadresse->cAdressZusatz}<br />{/if}
    {$Lieferadresse->cPLZ} {$Lieferadresse->cOrt}<br />{if $Lieferadresse->cBundesland}{$Lieferadresse->cBundesland}
        <br />
    {/if}
    {if $Lieferadresse->angezeigtesLand}{$Lieferadresse->angezeigtesLand}<br /><br />{/if}
{/if}
{if $Lieferadresse->cTel}{lang key='tel' section='account data'}: {$Lieferadresse->cTel}<br />{/if}
{if $Lieferadresse->cFax}{lang key='fax' section='account data'}: {$Lieferadresse->cFax}<br />{/if}
{if $Lieferadresse->cMobil}{lang key='mobile' section='account data'}: {$Lieferadresse->cMobil}<br />{/if}
{if $Lieferadresse->cMail}{$Lieferadresse->cMail}<br />{/if}
