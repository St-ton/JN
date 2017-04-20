{if $bestellschritt[1] != 3}
    <ul class="nav nav-wizard">
        <li class="{if $bestellschritt[1] == 1 || $bestellschritt[2] == 1}active{/if} col-xs-4">
            {if $bestellschritt[1] < 3 || $bestellschritt[2] < 3}<a href="bestellvorgang.php?editRechnungsadresse=1">{/if}
                {lang section='account data' key='billingAndDeliveryAddress'}
                {if $bestellschritt[1] < 3 || $bestellschritt[2] < 3}</a>{/if}
        </li>
        <li class="{if $bestellschritt[3] == 1 || $bestellschritt[4] == 1}active{/if} col-xs-4">
            {if $bestellschritt[3] < 3 || $bestellschritt[4] < 3}<a href="bestellvorgang.php?editZahlungsart=1">{/if}
                {lang section='account data' key='shippingAndPaymentOptions'}
                {if $bestellschritt[3] < 3 || $bestellschritt[4] < 3}</a>{/if}
        </li>
        <li class="{if $bestellschritt[5] == 1}active{/if} col-xs-4">
            {lang section='checkout' key='summary'}
        </li>
    </ul>
{/if}