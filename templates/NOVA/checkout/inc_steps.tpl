{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-inc-steps'}
    {assign var=step1_active value=($bestellschritt[1] == 1 || $bestellschritt[2] == 1)}
    {assign var=step2_active value=($bestellschritt[3] == 1 || $bestellschritt[4] == 1)}
    {assign var=step3_active value=($bestellschritt[5] == 1)}
    {if $bestellschritt[1] != 3}
        {nav class='stepper mb-4' tag='nav' aria=["label"=>"{lang key='secureCheckout' section='checkout'}"]}
            {col lg=4 class="nav-item step step-active {if $step1_active}step-current{else}col-auto{/if}"}
                {block name='checkout-inc-steps-first'}
                    {link href="{get_static_route id='bestellvorgang.php'}?editRechnungsadresse=1"
                        title="{lang section='account data' key='billingAndDeliveryAddress'}"
                        class="text-decoration-none"}
                        <div class="step-content">
                            <span class="badge badge-pill badge-primary mr-3 ml-md-auto">
                                <span class="badge-count">1</span>
                            </span>
                            <span class="step-text {if !$step1_active}d-none d-md-inline-block{/if} mr-auto">
                                {lang section='account data' key='billingAndDeliveryAddress'}
                            </span>
                            {if $step2_active || $step3_active}
                                <span class="fas fa-check ml-0 ml-md-3 mr-auto text-primary"></span>
                            {/if}
                        </div>
                    {/link}
                {/block}
            {/col}
            {col lg=4 class="nav-item step {if $step2_active || $step3_active}step-active{/if} {if $step2_active}step-current{else}col-auto{/if}"}
                {block name='checkout-inc-steps-second'}
                    {link href="{get_static_route id='bestellvorgang.php'}?editVersandart=1"
                        title="{lang section='account data' key='shippingAndPaymentOptions'}"
                        class="text-decoration-none"}
                        <div class="step-content">
                            <span class="badge badge-pill badge-{if $step2_active || $step3_active}primary{else}secondary{/if} mr-3 ml-md-auto">
                                <span class="badge-count">2</span>
                            </span>
                            <span class="step-text {if !$step2_active}d-none d-md-inline-block{/if} mr-auto">
                                {lang section='account data' key='shippingAndPaymentOptions'}
                            </span>
                            {if $step3_active}
                                <span class="fas fa-check ml-0 ml-md-3 mr-auto text-primary"></span>
                            {/if}
                        </div>
                    {/link}
                {/block}
            {/col}
            {col lg=4 class="nav-item step {if $step3_active}step-active step-current{else}col-auto{/if}"}
                {block name='checkout-inc-steps-third'}
                    <div class="step-content">
                        <span class="badge badge-pill badge-{if $step3_active}primary mr-3{else}secondary{/if} mr-md-3 ml-md-auto">
                            <span class="badge-count">3</span>
                        </span>
                        <span class="step-text {if !$step3_active}d-none d-md-inline-block{/if} mr-auto">
                            {lang section='checkout' key='summary'}
                        </span>
                    </div>
                {/block}
            {/col}
        {/nav}
    {/if}
{/block}
