{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-inc-delivery-address'}
    <ul class="list-unstyled">
        {if isset($orderDetail)}
            {if $Lieferadresse->cFirma}<li>{$Lieferadresse->cFirma}</li>{/if}
            {if $Lieferadresse->cZusatz}<li>{$Lieferadresse->cZusatz}</li>{/if}
            <li>{$Lieferadresse->cTitel} {$Lieferadresse->cVorname} {$Lieferadresse->cNachname}</li>
            <li>
                {$Lieferadresse->cStrasse} {$Lieferadresse->cHausnummer}, {if $Lieferadresse->cAdressZusatz}{$Lieferadresse->cAdressZusatz},{/if}
                {$Lieferadresse->cPLZ} {$Lieferadresse->cOrt},
                {if $Lieferadresse->cLand}{$Lieferadresse->cLand}{/if}
            </li>
        {else}
            {if $Lieferadresse->cFirma}<li>{$Lieferadresse->cFirma}</li>{/if}
            {if $Lieferadresse->cZusatz}<li>{$Lieferadresse->cZusatz}</li>{/if}
            <li>{$Lieferadresse->cTitel} {$Lieferadresse->cVorname} {$Lieferadresse->cNachname}</li>
            <li>{$Lieferadresse->cStrasse} {$Lieferadresse->cHausnummer}</li>
            {if $Lieferadresse->cAdressZusatz}<li>{$Lieferadresse->cAdressZusatz}</li>{/if}
            <li>{$Lieferadresse->cPLZ} {$Lieferadresse->cOrt}</li>
            {if $Lieferadresse->cBundesland}<li>{$Lieferadresse->cBundesland}</li>{/if}
            {if $Lieferadresse->angezeigtesLand}<li>{$Lieferadresse->angezeigtesLand}</li>{/if}
        {/if}
        {if $Lieferadresse->cTel}<li>{lang key='tel' section='account data'}: {$Lieferadresse->cTel}</li>{/if}
        {if $Lieferadresse->cFax}<li>{lang key='fax' section='account data'}: {$Lieferadresse->cFax}</li>{/if}
        {if $Lieferadresse->cMobil}<li>{lang key='mobile' section='account data'}: {$Lieferadresse->cMobil}</li>{/if}
        {if $Lieferadresse->cMail}<li>{$Lieferadresse->cMail}</li>{/if}
    </ul>
{/block}
