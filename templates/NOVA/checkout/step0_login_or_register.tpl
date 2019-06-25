{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-step0-login-or-register'}
    {block name='checkout-step0-login-or-register-alert'}
        {if !empty($fehlendeAngaben) && !$alertNote}
            {alert variant="danger"}{lang key='mandatoryFieldNotification' section='errorMessages'}{/alert}
        {/if}
        {if isset($fehlendeAngaben.email_vorhanden) && $fehlendeAngaben.email_vorhanden == 1}
            {alert variant="danger"}{lang key='emailAlreadyExists' section='account data'}{/alert}
        {/if}
        {if isset($fehlendeAngaben.formular_zeit) && $fehlendeAngaben.formular_zeit == 1}
            {alert variant="danger"}{lang key='formToFast' section='account data'}{/alert}
        {/if}
    {/block}
    {if isset($boxes.left) && !$bExclusive && !empty($boxes.left)}
        {assign var=withSidebar value=1}
    {else}
        {assign var=withSidebar value=0}
    {/if}
    {row id="register-customer"}
        {col cols=12 id="existing-customer" md="{if $withSidebar === 0}4{else}8{/if}"}
            {block name='checkout-step0-login-or-register-form-login'}
                {form method="post" action="{get_static_route id='bestellvorgang.php'}" class="evo-validate label-slide" id="order_register_or_login"}
                    {block name='checkout-step0-login-or-register-fieldset-form-login-content'}
                        <fieldset>
                            {block name='checkout-step0-login-or-register-headline-form-login-content'}
                                <div class="h2 mb-3">{lang key='alreadyCustomer'}</div>
                            {/block}
                            {block name='checkout-step0-login-or-register-include-customer-login'}
                                {include file='register/form/customer_login.tpl' withSidebar=$withSidebar}
                            {/block}
                        </fieldset>
                    {/block}
                {/form}
            {/block}
            <div class="hr-sect my-5">{lang key='or'}</div>
        {/col}
        {col cols=12 id="customer" md="{if $withSidebar === 0}8{else}12{/if}" class="mt-3"}
            {block name='checkout-step0-login-or-register-form'}
                {form method="post" action="{get_static_route id='bestellvorgang.php'}" class="form evo-validate label-slide" id="form-register"}
                    {block name='checkout-step0-login-or-register-include-customer-account'}
                        {include file='register/form/customer_account.tpl' checkout=1 step="formular"}
                        {row}
                            {col cols=12 md=8}
                                <hr class="my-4">
                            {/col}
                        {/row}
                    {/block}
                    {block name='checkout-step0-login-or-register-include-inc-shipping-address'}
                        {include file='checkout/inc_shipping_address.tpl'}
                    {/block}
                    {block name='checkout-step0-login-or-register-form-submit'}
                        <div class="text-left mt-5">
                            {input type="hidden" name="checkout" value="1"}
                            {input type="hidden" name="form" value="1"}
                            {input type="hidden" name="editRechnungsadresse" value="0"}
                            {button type="submit" variant="primary" class="submit_once"}
                                {lang key='sendCustomerData' section='account data'}
                            {/button}
                        </div>
                    {/block}
                {/form}
            {/block}
        {/col}
    {/row}
{/block}
