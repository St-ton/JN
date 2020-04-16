{block name='register-form'}
    {form action="{get_static_route id='registrieren.php'}" class="jtl-validate clearfix" slide=true}
        {block name='register-form-content'}
            {block name='register-form-include-customer-account'}
                {include file='register/form/customer_account.tpl'}
            {/block}
            {block name='register-form-hr'}
                <hr>
            {/block}
            {if isset($checkout) && $checkout === 1}
                {block name='register-form-include-inc-shipping-address'}
                    {include file='checkout/inc_shipping_address.tpl'}
                {/block}
            {/if}
            {block name='register-form-submit'}
                {row class='mt-5'}
                    {col cols=12 md=4 xl=3 class='ml-md-auto'}
                        {input type="hidden" name="checkout" value=$checkout|default:''}
                        {input type="hidden" name="form" value="1"}
                        {input type="hidden" name="editRechnungsadresse" value=$editRechnungsadresse}
                        {opcMountPoint id='opc_before_submit'}
                        {block name='register-form-submit-button'}
                            {button type="submit" value="1" variant="primary" class="submit_once" block=true}
                                {lang key='sendCustomerData' section='account data'}
                            {/button}
                        {/block}
                    {/col}
                {/row}
            {/block}
        {/block}
    {/form}
{/block}
