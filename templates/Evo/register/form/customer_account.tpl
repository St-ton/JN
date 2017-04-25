{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{if !empty($hinweis)}
    <div class="alert alert-info">{$hinweis}</div>{/if}
{if !empty($fehlendeAngaben) && !$hinweis}
    <div class="alert alert-danger">{lang key="yourDataDesc" section="account data"}</div>
{/if}
{if isset($fehlendeAngaben.email_vorhanden) && $fehlendeAngaben.email_vorhanden == 1}
    <div class="alert alert-danger">{lang key="emailAlreadyExists" section="account data"}</div>
{/if}
{if isset($fehlendeAngaben.formular_zeit) && $fehlendeAngaben.formular_zeit == 1}
    <div class="alert alert-danger">{lang key="formToFast" section="account data"}</div>
{/if}

{include file='checkout/inc_billing_address_form.tpl'}
{if !$editRechnungsadresse}
    {if isset($checkout) && $Einstellungen.kaufabwicklung.bestellvorgang_unregistriert === 'Y'}
        <div class="form-group">
            <input type="hidden" name="unreg_form" value="1">
            <label class="control-label" for="checkout_create_account_unreg" data-toggle="collapse" data-target="#create_account_data">
                <input id="checkout_create_account_unreg" class="checkbox-inline" type="checkbox" name="unreg_form" value="0" checked="checked" />
                {lang key="createNewAccount" section="account data"}
            </label>
        </div>
    {/if}
    <div id="create_account_data" class="row collapse in collapse-non-validate" aria-expanded="true">
        <div class="col-xs-6">
            <div class="form-group float-label-control{if isset($fehlendeAngaben.pass_zu_kurz) || isset($fehlendeAngaben.pass_ungleich)} has-error{/if} required">
                <label for="password" class="control-label">{lang key="password" section="account data"}</label>
                <input type="password" name="pass" maxlength="20" id="password" class="form-control" placeholder="{lang key="password" section="account data"}" required>
                {if isset($fehlendeAngaben.pass_zu_kurz)}
                    <div class="form-error-msg text-danger"><i class="fa fa-warning"></i> {$warning_passwortlaenge}</div>
                {/if}
            </div>
        </div>
        <div class="col-xs-6">
            <div class="form-group float-label-control{if isset($fehlendeAngaben.pass_ungleich)} has-error{/if} required">
                <label for="password2" class="control-label">{lang key="passwordRepeat" section="account data"}</label>
                <input type="password" name="pass2" maxlength="20" id="password2" class="form-control" placeholder="{lang key="passwordRepeat" section="account data"}" required>
                {if isset($fehlendeAngaben.pass_ungleich)}
                    <div class="form-error-msg text-danger"><i class="fa fa-warning"></i> {lang key="passwordsMustBeEqual" section="account data"}</div>
                {/if}
            </div>
        </div>
    </div>
{/if}