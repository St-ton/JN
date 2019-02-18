{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<h1>{lang key='deleteAccount' section='login'}</h1>

{alert variant="danger"}{lang key='reallyDeleteAccount' section='login'}{/alert}

{form id="delete_account" action="{get_static_route id='jtl.php'}" method="post"}
    {input type="hidden" name="del_acc" value="1"}
    {button type="submit" value="1" class="w-auto" variant="danger"}
        {lang key='deleteAccount' section='login'}
    {/button}
{/form}
