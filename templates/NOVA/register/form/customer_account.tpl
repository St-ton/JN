{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='register-form-customer-account'}
    {block name='register-form-customer-account-include-inc-billing-address-form'}
        {include file='checkout/inc_billing_address_form.tpl'}
    {/block}
    {assign var=unregForm value=0}
    {block name='register-form-customer-account-content'}
        {if !$editRechnungsadresse}
            {block name='register-form-customer-account-unreg'}
                {if !$smarty.session.Warenkorb->hasDigitalProducts() && isset($checkout)
                    && $Einstellungen.kaufabwicklung.bestellvorgang_unregistriert === 'Y'}
                    <div class="form-group checkbox control-toggle">
                        {input type="hidden" name="unreg_form" value="1"}
                        {checkbox id="checkout_create_account_unreg"
                            name="unreg_form" value="0" checked=($unregForm === 0)
                            data=["toggle"=>"collapse", "target"=>"#create_account_data"]}
                            {lang key='createNewAccount' section='account data'}
                        {/checkbox}
                    </div>
                {else}
                    {input type="hidden" name="unreg_form" value="0"}
                {/if}
            {/block}
            {block name='register-form-customer-account-password'}
                {row id="create_account_data" class="collapse collapse-non-validate {if $unregForm === 1}hidden{else}show{/if}" aria-expanded="true"}
                    {block name='register-form-customer-account-password-first'}
                        {col cols=12 md=4}
                            <div class="form-group d-flex flex-column {if isset($fehlendeAngaben.pass_zu_kurz) || isset($fehlendeAngaben.pass_ungleich)} has-error{/if}" role="group">
                                <label for="pass" class="col-form-label">
                                    {lang key='password' section='account data'}
                                </label>
                                <input type="password"
                                       class="form-control"
                                       placeholder="{lang key='password' section='account data'}"
                                       id="password"
                                       required=""
                                       value=""
                                       name="pass"
                                       aria-autocomplete="none"
                                       autocomplete="off"
                                       disabled={$unregForm === 1}>
                            </div>
                            {block name='account-change-password-include-password-check'}
                                {include file='snippets/password_check.tpl' id='#password'}
                            {/block}
                        {/col}
                    {/block}
                    {block name='register-form-customer-account-password-repeat'}
                        {col cols=12 md=4}
                            {formgroup
                                class="{if isset($fehlendeAngaben.pass_zu_kurz) || isset($fehlendeAngaben.pass_ungleich)} has-error{/if}"
                                label="{lang key='passwordRepeat' section='account data'}"
                                label-for="password2"
                            }
                                {input
                                    type="password"
                                    name="pass2"
                                    maxlength="20"
                                    id="password2"
                                    placeholder="{lang key='passwordRepeat' section='account data'}"
                                    required=true
                                    data=["must-equal-to"=>"#create_account_data input[name='pass']",
                                        "custom-message"=>"{lang key='passwordsMustBeEqual' section='account data'}"]
                                    autocomplete="off"
                                    aria-autocomplete="none"
                                    disabled=($unregForm === 1)
                                    value=""
                                }
                                {if isset($fehlendeAngaben.pass_ungleich)}
                                    <div class="form-error-msg text-danger"><i class="fa fa-exclamation-triangle"></i>
                                        {lang key='passwordsMustBeEqual' section='account data'}
                                    </div>
                                {/if}
                            {/formgroup}
                        {/col}
                    {/block}
                {/row}
            {/block}
        {/if}
    {/block}
{/block}
