{if $bestellschritt[1] != 3 }
<div class="stepwizard" id="checkout_steps">
    <ol class="list-unstyled stepwizard-row row">
        <li class="col-md-1 hidden-xs"></li>
        <li class="col-sm-3 col-md-2 stepwizard-step step2 state{$bestellschritt[1]} first{if $bestellschritt[1]==3} text-muted{/if}">
            {if $bestellschritt[1]==2}<a href="bestellvorgang.php?editRechnungsadresse=1">{/if}
            <span {if $bestellschritt[1]==1}class="btn btn-circle btn-primary"{else}class="btn btn-circle btn-default"{if $bestellschritt[1]!=2} disabled{/if}{/if}>1</span><div class="step-name hidden-xs">{lang key="billingAdress" section="checkout"}</div>
            {if $bestellschritt[1]==2}</a>{/if}
        </li>
        <li class="col-sm-2 stepwizard-step step3 state{$bestellschritt[2]}{if $bestellschritt[2]==3} text-muted{/if}">
            {if $bestellschritt[2]==2}<a href="bestellvorgang.php?editLieferadresse=1">{/if}
            <span {if $bestellschritt[2]==1}class="btn btn-circle btn-primary"{else}class="btn btn-circle btn-default"{if $bestellschritt[2]!=2} disabled{/if}{/if}>2</span><div class="step-name hidden-xs">{lang key="shippingAdress" section="checkout"}</div>
            {if $bestellschritt[2]==2}</a>{/if}
        </li>
        <li class="col-sm-2 stepwizard-step step4 state{$bestellschritt[3]}{if $bestellschritt[3]==3} text-muted{/if}">
            {if $bestellschritt[3]==2}<a href="bestellvorgang.php?editVersandart=1">{/if}
            <span {if $bestellschritt[3]==1}class="btn btn-circle btn-primary"{else}class="btn btn-circle btn-default"{if $bestellschritt[3]!=2} disabled{/if}{/if}>3</span><div class="step-name hidden-xs">{lang key="shipmentMode" section="checkout"}</div>
            {if $bestellschritt[3]==2}</a>{/if}
        </li>
        <li class="col-sm-2 stepwizard-step step5 state{$bestellschritt[4]}{if $bestellschritt[4]==3} text-muted{/if}">
            {if $bestellschritt[4]==2 || $step=='ZahlungZusatzschritt'}<a href="bestellvorgang.php?editZahlungsart=1">{/if}
            <span {if $bestellschritt[4]==1}class="btn btn-circle btn-primary"{else}class="btn btn-circle btn-default"{if $bestellschritt[4]!=2} disabled{/if}{/if}>4</span><div class="step-name hidden-xs">{lang key="paymentMethod" section="checkout"}</div>
            {if $bestellschritt[4]==2 || $step=='ZahlungZusatzschritt'}</a>{/if}
        </li>
        <li class="col-sm-2 stepwizard-step step6 state{$bestellschritt[5]}{if $bestellschritt[5]==3} text-muted{/if}">
            <span {if $bestellschritt[5]==1}class="btn btn-circle btn-primary"{else}class="btn btn-circle btn-default"{if $bestellschritt[5]!=2} disabled{/if}{/if}>5</span><div class="step-name hidden-xs">{lang key="summary" section="checkout"}</div>
        </li>
    </ol>
</div>
<div class="clearfix top15"></div>
{/if}