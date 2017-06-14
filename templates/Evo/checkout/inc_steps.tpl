{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{if $bestellschritt[1] != 3}
    <ul class="nav nav-wizard row">
        <li class="{if $bestellschritt[1] == 1 || $bestellschritt[2] == 1}active col-xs-8{else}col-xs-2{/if} col-md-4">
            {if $bestellschritt[1] < 3 || $bestellschritt[2] < 3}
            <a href="bestellvorgang.php?editRechnungsadresse=1">{lang section='account data' key='billingAndDeliveryAddress'}</a>
            {else}
            <span class="nav-badge">{lang section='account data' key='billingAndDeliveryAddress'}</span>
            {/if}
        </li>
        <li class="{if $bestellschritt[3] == 1 || $bestellschritt[4] == 1}active col-xs-8{else}col-xs-2{/if} col-md-4">
            {if $bestellschritt[3] < 3 || $bestellschritt[4] < 3}
            <a href="bestellvorgang.php?editZahlungsart=1">{lang section='account data' key='shippingAndPaymentOptions'}</a>
            {else}
                <span class="nav-badge">{lang section='account data' key='shippingAndPaymentOptions'}</span>
            {/if}
        </li>
        <li class="{if $bestellschritt[5] == 1}active col-xs-8{else}col-xs-2{/if} col-md-4">
            <span class="nav-badge">{lang section='checkout' key='summary'}</span>
        </li>
    </ul>
{/if}