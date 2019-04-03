{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($fehlendeAngaben) && !$alertNote}
    {alert variant="danger"}{lang key='mandatoryFieldNotification' section='errorMessages'}{/alert}
{/if}
{if isset($fehlendeAngaben.email_vorhanden) && $fehlendeAngaben.email_vorhanden == 1}
    {alert variant="danger"}{lang key='emailAlreadyExists' section='account data'}{/alert}
{/if}
{if isset($fehlendeAngaben.formular_zeit) && $fehlendeAngaben.formular_zeit == 1}
    {alert variant="danger"}{lang key='formToFast' section='account data'}{/alert}
{/if}
{if isset($boxes.left) && !$bExclusive && !empty($boxes.left)}
    {assign var=withSidebar value=1}
{else}
    {assign var=withSidebar value=0}
{/if}
{row id="register-customer"}
    {col cols=12 id="existing-customer" md="{if $withSidebar === 0}4{else}12{/if}"}
        {form method="post" action="{get_static_route id='bestellvorgang.php'}" class="evo-validate" id="order_register_or_login"}
            {block name='checkout-login'}
                {block name='checkout-login-body'}
                    <fieldset>
                        <legend>{block name='checkout-login-title'}{lang key='alreadyCustomer'}{/block}</legend>
                        {include file='register/form/customer_login.tpl' withSidebar=$withSidebar}
                    </fieldset>
                {/block}
            {/block}
        {/form}
        <div class="hr-sect my-5">{lang key='or'}</div>
    {/col}
    {col cols=12 id="customer" md="{if $withSidebar === 0}8{else}12{/if}" class="mt-3"}
        {form method="post" action="{get_static_route id='bestellvorgang.php'}" class="form evo-validate" id="form-register"}
            {block name='checkout-register'}
                {block name='checkout-register-body'}
                    {include file='register/form/customer_account.tpl' checkout=1 step="formular"}
                    <hr/>
                    {include file='checkout/inc_shipping_address.tpl'}
                {/block}
            {/block}
            <div class="text-right">
                {input type="hidden" name="checkout" value="1"}
                {input type="hidden" name="form" value="1"}
                {input type="hidden" name="editRechnungsadresse" value="0"}
                {button type="submit" variant="primary" class="submit_once"}
                    {lang key='sendCustomerData' section='account data'}
                {/button}
            </div>
        {/form}
    {/col}
{/row}
