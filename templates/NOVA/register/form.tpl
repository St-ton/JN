{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !isset($checkout)}
    {include file='register/inc_vcard_upload.tpl' id='registrieren.php'}
{/if}

{form action="{get_static_route id='registrieren.php'}" class="evo-validate"}
    {include file='register/form/customer_account.tpl'}
    <hr>
    {if isset($checkout) && $checkout === 1}
        {include file='checkout/inc_shipping_address.tpl'}
    {/if}
    {input type="hidden" name="checkout" value=$checkout|default:''}
    {input type="hidden" name="form" value="1"}
    {input type="hidden" name="editRechnungsadresse" value=$editRechnungsadresse}
    {button type="submit" value="1" variant="primary" size="lg" class="float-right submit_once"}
        {lang key='sendCustomerData' section='account data'}
    {/button}
{/form}
