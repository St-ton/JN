<h1>{lang key="editBillingAdress" section="account data"}</h1>

{if !$hinweis}
    <div class="alert alert-info">{lang key="editBillingAdressDesc" section="login"}</div>
{else}
    <div class="alert alert-danger">{$hinweis}</div>
{/if}

{include file="snippets/extension.tpl"}
<form id="rechnungsdaten" action="jtl.php" method="post" class="well panel-wrap">
    <div class="panel panel-default" id="panel-address-form">
        <div class="panel-body">
            {$jtl_token}
            {include file='checkout/inc_billing_address_form.tpl'}

            <input type="hidden" name="editRechnungsadresse" value="1" />
            <input type="hidden" name="edit" value="1" />

            <div class="form-group">
                <input type="submit" class="btn btn-primary submit" value="{lang key="editBillingAdress" section="account data"}" />
            </div>
        </div>
    </div>
</form>
