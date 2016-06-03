{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="login"}
{config_load file="$lang.conf" section="shopupdate"}

<script type="text/javascript">
    {literal}
    $(document).ready(function () {
        $("input.field:first").focus();
    });
    {/literal}
</script>
<div class="vertical-center">
    <div class="container">
        <div id="login_wrapper">
            <p class="text-center">
                <img src="{$currentTemplateDir}gfx/shop-login.png" alt="JTL-Shop" class="logo" />
            </p>
            <div id="login_outer" class="panel panel-default">
                <div class="panel-body">
                
                    {if isset($cFehler) && $cFehler}
                        <div class="alert alert-danger">{$cFehler}</div>
                        <script type="text/javascript">
                            {literal}
                            $(document).ready(function () {
                                $("#login_wrapper").effect("shake", {times: 2}, 50);
                            });
                            {/literal}
                        </script>
                    {elseif isset($pw_updated) && $pw_updated === true}
                        <div class="alert alert-success" role="alert"><i class="fa fa-info-circle"></i> Passwort wurde erfolgreich ge&auml;ndert.</div>
                    {else}
                        <p class="text-muted">{#login#}</p>
                    {/if}

                    <form method="post" action="index.php" class="form-horizontal" role="form">
                        {$jtl_token}
                        <input id="benutzer" type="hidden" name="adminlogin" value="1" />
                        {if isset($uri) && $uri|count_characters > 0}
                            <input type="hidden" name="uri" value="{$uri}" />
                        {/if}
                        {if isset($code_adminlogin) && $code_adminlogin}
                            <input type="hidden" name="md5" value="{$code_adminlogin->codemd5}" id="captcha_md5">{/if}
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input class="form-control" type="text" placeholder="{#username#}" name="benutzer" id="user_login" value="" size="20" tabindex="10" />
                            </div>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input class="form-control" type="password" placeholder="{#password#}" name="passwort" id="user_pass" value="" size="20" tabindex="20" />
                            </div>
                            {if isset($code_adminlogin) && $code_adminlogin}
                                <div class="captcha">
                                    <img src="{$code_adminlogin->codeURL}" alt="{#code#}" id="captcha" />
                                </div>
                                <a href="index.php" class="captcha">{#reloadCaptcha#}</a>
                                <p>
                                    <input class="form-control" type="text" name="captcha" tabindex="30" id="captcha_text" placeholder="{#enterCode#}" />
                                </p>
                            {/if}
                        <button type="submit" value="Anmelden" tabindex="100" class="btn btn-primary btn-block btn-md">Anmelden</button>
                    </form>
                </div>
            </div>
            <p class="forgot-pw-wrap">
                <a href="pass.php" title="Passwort vergessen"><i class="fa fa-lock"></i> Passwort vergessen?</a>
            </p>
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}