{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<h1>{lang key='deleteAccount' section='login'}</h1>

{alert variant="danger"}{lang key='reallyDeleteAccount' section='login'}{/alert}

{form id="delete_account" action="{get_static_route id='jtl.php'}" method="post"}
    {$jtl_token}
    {input type="hidden" name="del_acc" value="1"}
    {input type="submit" class="btn btn-danger w-auto" value="{lang key='deleteAccount' section='login'}"}
{/form}
