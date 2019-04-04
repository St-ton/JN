{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='account-delete-account'}
    {block name='heading'}
        <h1>{lang key='deleteAccount' section='login'}</h1>
    {/block}
    {block name='account-delete-account-alert'}
        {alert variant="danger"}{lang key='reallyDeleteAccount' section='login'}{/alert}
    {/block}
    {block name='account-delete-account-form'}
        {form id="delete_account" action="{get_static_route id='jtl.php'}" method="post"}
            {block name='account-delete-account-form-submit'}
            {input type="hidden" name="del_acc" value="1"}
            {button type="submit" value="1" class="w-auto" variant="danger"}
                {lang key='deleteAccount' section='login'}
            {/button}
            {/block}
        {/form}
    {/block}
{/block}
