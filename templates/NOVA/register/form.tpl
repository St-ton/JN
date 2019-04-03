{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='register-form'}
    {if !isset($checkout)}
        {block name='register-form-include-inc-vcard-upload'}
            {include file='register/inc_vcard_upload.tpl' id='registrieren.php'}
        {/block}
    {/if}

    {block name='register-form-form'}
        {form action="{get_static_route id='registrieren.php'}" class="evo-validate"}
            {block name='register-form-content'}
                {block name='register-form-include-customer-account'}
                    {include file='register/form/customer_account.tpl'}
                {/block}
                <hr>
                {if isset($checkout) && $checkout === 1}
                    {block name='register-form-include-inc-shipping-address'}
                        {include file='checkout/inc_shipping_address.tpl'}
                    {/block}
                {/if}
                {block name='register-form-submit'}
                    {input type="hidden" name="checkout" value=$checkout|default:''}
                    {input type="hidden" name="form" value="1"}
                    {input type="hidden" name="editRechnungsadresse" value=$editRechnungsadresse}
                    {button type="submit" value="1" variant="primary" size="lg" class="float-right submit_once"}
                        {lang key='sendCustomerData' section='account data'}
                    {/button}
                {/block}
            {/block}
        {/form}
    {/block}
{/block}
