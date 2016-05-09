<h1>{lang key="deleteAccount" section="login"}</h1>

{if !$hinweis}
    <div class="alert alert-danger">{lang key="reallyDeleteAccount" section="login"}</div>
{else}
    <div class="alert alert-danger">{$hinweis}</div>
{/if}

<form id="delete_account" action="jtl.php" method="post">
    {$jtl_token}
    <input type="hidden" name="del_acc" value="1" />
    <input type="submit" class="submit btn btn-danger" value="{lang key="deleteAccount" section="login"}" />
</form>