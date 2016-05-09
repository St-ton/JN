<h1>{lang key="changePassword" section="login"}</h1>

{include file="snippets/extension.tpl"}

{block name="change-password-form"}
<div class="well panel-wrap">
    <div class="panel panel-default">
        <div class="panel-body">
            {if !$hinweis}
                <p class="alert alert-info">{lang key="changePasswordDesc" section="login"}</p>
            {else}
                <p class="alert alert-danger">{$hinweis}</p>
            {/if}
            <div class="row">
                <form id="password" action="jtl.php" method="post" class="col-xs-8 col-xs-offset-2 col-md-5 col-md-offset-3 col-lg-4 col-lg-offset-4">
                    {$jtl_token}
                    <div class="form-group required">
                        <label for="currentPassword" class="control-label">{lang key="currentPassword" section="login"}</label>
                        <input type="password" name="altesPasswort" id="currentPassword" class="form-control" required>
                    </div>

                    <div class="form-group required">
                        <label for="newPassword" class="control-label">{lang key="newPassword" section="login"}</label>
                            <input type="password" name="neuesPasswort1" id="newPassword" class="form-control" required>
                    </div>

                    <div class="form-group required">
                        <label for="newPasswordRpt" class="control-label">{lang key="newPasswordRpt" section="login"}</label>
                        <input type="password" name="neuesPasswort2" id="newPasswordRpt" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <input type="hidden" name="pass_aendern" value="1">
                        <input type="submit" value="{lang key="changePassword" section="login"}" class="submit btn btn-primary btn-block">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{/block}