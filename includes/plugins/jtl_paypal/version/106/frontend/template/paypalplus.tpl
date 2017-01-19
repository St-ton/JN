<script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js" type="text/javascript"></script>    

{if $hinweis}
    <div class="alert alert-danger">{$hinweis}</div>
{/if}

<div class="row">    
    <div class="col-xs-12">
        <div class="panel-wrap">
            <fieldset>
            {if !empty($cFehler)}
                <div class="alert alert-danger">{$cFehler}</div>
            {/if}
            <div id="pp-plus">
                <div id="ppp-container"></div>
            </div>
            </fieldset>
            {if $embedded}
                <input id="ppp-submit" type="submit" value="{lang key="continueOrder" section="account data"}" class="btn btn-primary submit btn-lg pull-right" />
            {else}
                {block name="checkout-payment-options-body"}
                <form id="zahlung" method="post" action="bestellvorgang.php" class="form">
                    {$jtl_token}
                    <fieldset>
                        <ul class="list-group">
                            {foreach name=paymentmethod from=$Zahlungsarten item=zahlungsart}
                                <li id="{$zahlungsart->cModulId}" class="list-group-item">
                                    <div class="radio">
                                        <label for="payment{$zahlungsart->kZahlungsart}" class="btn-block">
                                            <input name="Zahlungsart" value="{$zahlungsart->kZahlungsart}" type="radio" id="payment{$zahlungsart->kZahlungsart}"{if $Zahlungsarten|@count == 1} checked{/if}{if $smarty.foreach.paymentmethod.first} required{/if}>
                                                {if $zahlungsart->cBild}
                                                    <img src="{$zahlungsart->cBild}" alt="{$zahlungsart->angezeigterName|trans}" class="vmiddle">
                                                {else}
                                                    <strong>{$zahlungsart->angezeigterName|trans}</strong>
                                                {/if}
                                            {if $zahlungsart->fAufpreis != 0}
                                                <span class="badge pull-right">
                                                {if $zahlungsart->cGebuehrname|has_trans}
                                                    <span>{$zahlungsart->cGebuehrname|trans} </span>
                                                {/if}
                                                {$zahlungsart->cPreisLocalized}
                                                </span>
                                            {/if}
                                            {if $zahlungsart->cHinweisText|has_trans}
                                                <p class="small text-muted">{$zahlungsart->cHinweisText|trans}</p>
                                            {/if}
                                        </label>
                                    </div>
                                </li>
                            {/foreach}
                        </ul>
                        
                        <!-- trusted shops? -->
                        
                        <input type="hidden" name="zahlungsartwahl" value="1" />
                    </fieldset>
                    <input id="ppp-submit" type="submit" value="{lang key="continueOrder" section="account data"}" class="btn btn-primary submit btn-lg pull-right" />
                </form>
                {/block}
            {/if}
        </div>
    </div>
</div>

<script type="application/javascript">
var submit = 'ppp-submit';
var thirdPartyPayment = false;
var ppConfig = {ldelim}
    approvalUrl: "{$approvalUrl}",
    placeholder: "ppp-container",
    mode: "{$mode}",
{if $mode == 'sandbox'}
    showPuiOnSandbox: true,
{/if}
    buttonLocation: "outside",
    preselection: "paypal",
    disableContinue: function() {ldelim}
        if ($('#zahlung input[type="radio"]:checked').length == 0) {ldelim}
            $('#zahlung input[type="radio"]:first')
                .prop('checked', true);
        {rdelim}
    {rdelim},
    enableContinue: function() {ldelim}
        $('#zahlung input[type="radio"]')
            .prop('checked', false);
    {rdelim},
    showLoadingIndicator: true,
    language: "{$language}",
    country: "{$country}",
    onThirdPartyPaymentMethodSelected: function(data) {ldelim}
        thirdPartyPayment = true;
    {rdelim},
    onThirdPartyPaymentMethodDeselected: function(data) {ldelim}
        thirdPartyPayment = false;
    {rdelim},
    onContinue: function() {ldelim}
        if (thirdPartyPayment) {ldelim}
            PAYPAL.apps.PPP.doCheckout();
        {rdelim} else {ldelim}
            $('#' + submit).attr('disabled', true);
            $.get("index.php", {ldelim} s: "{$linkId}", a: "payment_patch", id: "{$paymentId}" {rdelim})
                .success(function() {ldelim}
                    PAYPAL.apps.PPP.doCheckout();
                {rdelim})
                .fail(function(res) {ldelim}
                    $('#' + submit).attr('disabled', false);
                    var error = JSON.parse(res.responseText);
                    var errorText = 'Unknown error';
                    if (error && error.message) {
                        errorText = error.message;
                    }
                    alert(errorText);
                {rdelim});
        {rdelim}
    {rdelim},
    showLoadingIndicator: true,
    {if $styles}
        styles: {$styles|@json_encode},
    {/if}
    {if $thirdPartyPaymentMethods|@count > 0}
        thirdPartyPaymentMethods: {$thirdPartyPaymentMethods|@json_encode}
    {/if}
{rdelim};

try {
    var ppp = PAYPAL.apps.PPP(ppConfig);
} catch (d) { }

$(document).ready(function() {ldelim}
    $('#' + submit).click(function() {ldelim}
        var checked = $('#zahlung input[type="radio"]:checked');
        if ($(checked).length > 0) {ldelim}
            return true;
        {rdelim}
        ppp.doContinue();
        return false;
    {rdelim});
    
    $('#zahlung input[type="radio"]').change(function() {ldelim}
        ppp.deselectPaymentMethod();
    {rdelim});
{rdelim});
</script>
