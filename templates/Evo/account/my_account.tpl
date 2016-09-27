<h1 class="menu-title">{lang key="welcome" section="login"} {$Kunde->cAnredeLocalized} {$smarty.session.Kunde->cNachname}</h1>

<div class="row">
    <div class="col-xs-12 col-md-6">
        {block name="account-billing-address"}
            <div class="panel panel-default" id="panel-billing-address">
                <div class="panel-heading">
                    <h3 class="panel-title">{block name="account-billing-address-title"}{lang key="billingAdress" section="account data"}{/block}</h3>
                </div>
                <div class="panel-body">
                    {block name="account-billing-address-body"}
                        {include file='checkout/inc_billing_address.tpl' additional=false}
                        <p>
                            <a class="btn btn-default btn-sm top15" href="{get_static_route id='jtl.php' params=['editRechnungsadresse' => 1]}">
                                <span class="fa fa-home"></span> {lang key="modifyBillingAdress" section="global"}
                            </a>
                        </p>
                    {/block}
                </div>
            </div>
        {/block}
    </div>

    <div class="col-xs-12 col-md-6">

        {block name="account-credit"}
            <div class="panel panel-default">
                <div class="panel-body">
                    {lang key="yourMoneyOnAccount" section="login"}: <strong>{$Kunde->cGuthabenLocalized}</strong>
                </div>
            </div>
        {/block}

        {block name="account-general"}
            <div class="panel-group" id="account-general" role="tablist" aria-multiselectable="true">
                <div class="btn-group btn-group-justified">
                    {if $Einstellungen.kundenwerbenkunden.kwk_nutzen === 'Y'}
                        <a class="btn btn-default" href="{get_static_route id='jtl.php'}?KwK=1">
                            <span class="fa fa-comment"></span> {lang key="kwkName" section="login"}
                        </a>
                    {/if}
                    <a class="btn btn-default" href="{get_static_route id='jtl.php'}?pass=1">
                        <span class="fa fa-lock"></span> {lang key="changePassword" section="login"}
                    </a>
                    <a class="btn btn-danger" href="{get_static_route id='jtl.php'}?del=1">
                        <span class="fa fa-chain-broken"></span> {lang key="deleteAccount" section="login"}
                    </a>
                </div>
            </div>
        {/block}
    </div>
</div>

{include file='account/downloads.tpl'}