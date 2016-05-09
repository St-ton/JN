<form id="form_payment_extra" class="form payment_extra" method="post" action="bestellvorgang.php">
    {$jtl_token}
    <div class="row">
        <div class="col-xs-12 col-md-10 col-md-offset-1">
            <div class="well panel-wrap">
                <div class="panel panel-default" id="order-additional-payment">
                    <div class="panel-body">
                        {include file=$Zahlungsart->cZusatzschrittTemplate}
                        <input type="hidden" name="zahlungsartwahl" value="1" />
                        <input type="hidden" name="zahlungsartzusatzschritt" value="1" />
                        <input type="hidden" name="Zahlungsart" value="{$Zahlungsart->kZahlungsart}" />
                        <input type="submit" value="{lang key="continueOrder" section="account data"}" class="submit btn btn-primary pull-right" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>