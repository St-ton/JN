{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-inc-steps'}
    {assign var=step1_active value=($bestellschritt[1] == 1 || $bestellschritt[2] == 1)}
    {assign var=step2_active value=($bestellschritt[3] == 1 || $bestellschritt[4] == 1)}
    {assign var=step3_active value=($bestellschritt[5] == 1)}
    {if $bestellschritt[1] != 3}
        {nav pills=true fill=true class="mb-3 nav-wizard"}
            {link href="{get_static_route id='bestellvorgang.php'}?editRechnungsadresse=1"
                title="{lang section='account data' key='billingAndDeliveryAddress'}"
                class="nav-item nav-link {if $step1_active}active{/if}"
            }
                <span class="align-items-center {if !$step1_active}d-none d-md-flex{/if}">
                    <div class="nav-number active">1</div>
                    {lang section='account data' key='billingAndDeliveryAddress'}
                    {if $step2_active || $step3_active}<i class="fas fa-check"></i>{/if}
                </span>
            {/link}

            {link href="{get_static_route id='bestellvorgang.php'}?editVersandart=1"
                title="{lang section='account data' key='shippingAndPaymentOptions'}"
                class="nav-item nav-link {if $step2_active}active{/if} {if $step1_active}nav-todo{/if}"
            }
                <span class="align-items-center {if !$step2_active}d-none d-md-flex{/if}">
                    <div class="nav-number {if $step2_active || $step3_active}active{/if}">2</div>
                    {lang section='account data' key='shippingAndPaymentOptions'}
                    {if $step3_active}<i class="fas fa-check"></i>{/if}
                </span>
            {/link}

            <span class="nav-item nav-link {if $step3_active}active{/if} {if $step1_active || $step2_active}nav-todo{/if}">
                <span class="align-items-center {if !$step3_active}d-none d-md-flex{/if}">
                    <div class="nav-number {if $step3_active}active{/if}">3</div>
                    {lang section='checkout' key='summary'}
                </span>
            </span>
        {/nav}
    {/if}
{/block}
