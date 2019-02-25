{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{assign var=step1_active value=($bestellschritt[1] == 1 || $bestellschritt[2] == 1)}
{assign var=step2_active value=($bestellschritt[3] == 1 || $bestellschritt[4] == 1)}
{assign var=step3_active value=($bestellschritt[5] == 1)}
{if $bestellschritt[1] != 3}
    {*{row class="nav-wizard"}
        {col cols="{if $step1_active}8 {else}2{/if}"
            sm="{if $step1_active}6{else}3{/if}"
            md=4
            class="{if $step1_active}active{/if}"}
            {if $bestellschritt[1] < 3 || $bestellschritt[2] < 3}
                {link href="{get_static_route id='bestellvorgang.php'}?editRechnungsadresse=1" title="{lang section='account data' key='billingAndDeliveryAddress'}"}
                    <i class="fa fa-user d-md-none{if $step1_active} d-none{/if}"></i>
                    <span class="{if !$step1_active}d-none d-md-flex{/if}">{lang section='account data' key='billingAndDeliveryAddress'}</span>
                {/link}
            {else}
                <span class="nav-badge">
                    <i class="fa fa-user d-md-none{if $step1_active} d-none{/if}"></i>
                    <span class="{if !$step1_active}d-none d-md-flex{/if}">{lang section='account data' key='billingAndDeliveryAddress'}</span>
                </span>
            {/if}
        {/col}
        {col cols="{if $step2_active}8 {else}2{/if}"
            sm="{if $step2_active}6{else}3{/if}"
            md=4
            class="{if $step1_active}active{/if}"}
            {if $bestellschritt[3] < 3 || $bestellschritt[4] < 3}
                {link href="{get_static_route id='bestellvorgang.php'}?editVersandart=1" title="{lang section='account data' key='shippingAndPaymentOptions'}"}
                    <i class="fa fa-truck d-md-none{if $step2_active} d-none{/if}"></i>
                    <span class="{if !$step2_active}d-none d-md-flex{/if}">{lang section='account data' key='shippingAndPaymentOptions'}</span>
                {/link}
            {else}
                <span class="nav-badge">
                    <i class="fa fa-truck d-md-none{if $step2_active} d-none{/if}"></i>
                    <span class="{if !$step2_active}d-none d-md-flex{/if}">{lang section='account data' key='shippingAndPaymentOptions'}</span>
                </span>
            {/if}
        {/col}
        {col cols="{if $step3_active}8 {else}2{/if}"
            sm="{if $step3_active}6{else}3{/if}"
            md=4
            class="{if $step1_active}active{/if}"}
            <span class="nav-badge">
                <i class="fab fa-wpforms d-md-none{if $step3_active} d-none{/if}"></i>
                <span class="{if !$step3_active}d-none d-md-flex{/if}">{lang section='checkout' key='summary'}</span>
            </span>
        {/col}
    {/row}*}
    {nav pills=true fill=true class="mb-3 nav-wizard"}
        {link href="{get_static_route id='bestellvorgang.php'}?editRechnungsadresse=1"
            title="{lang section='account data' key='billingAndDeliveryAddress'}"
            class="nav-item nav-link {if $step1_active}active{/if}"
        }
            <i class="fas fa-user d-md-none{if $step1_active} d-none{/if}"></i>
            <span class="{if !$step1_active}d-none d-md-flex{/if}">{lang section='account data' key='billingAndDeliveryAddress'}</span>
        {/link}


        {link href="{get_static_route id='bestellvorgang.php'}?editVersandart=1"
            title="{lang section='account data' key='shippingAndPaymentOptions'}"
            class="nav-item nav-link {if $step2_active}active{/if}"
        }
            <i class="fas fa-truck d-md-none{if $step2_active} d-none{/if}"></i>
            <span class="{if !$step2_active}d-none d-md-flex{/if}">{lang section='account data' key='shippingAndPaymentOptions'}</span>
        {/link}


        <span class="nav-item nav-link">
                <i class="fab fa-wpforms d-md-none{if $step3_active} d-none{/if}"></i>
                <span class="{if !$step3_active}d-none d-md-flex{/if}">{lang section='checkout' key='summary'}</span>
        </span>
    {/nav}
{/if}
