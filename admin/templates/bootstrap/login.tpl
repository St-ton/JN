{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='login'}
{config_load file="$lang.conf" section='shopupdate'}

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
            <div id="login_outer" class="card">
                <div class="card-body">
                    {if $alertError}
                        {include file='snippets/alert_list.tpl'}
                        <script type="text/javascript">
                            {literal}
                            $(document).ready(function () {
                                $("#login_wrapper").effect("shake", {times: 2}, 50);
                            });
                            {/literal}
                        </script>
                    {elseif isset($pw_updated) && $pw_updated === true}
                        <div class="alert alert-success" role="alert"><i class="fal fa-info-circle"></i> {__('successPasswordChange')}</div>
                    {else}
                        {if !isset($smarty.session.AdminAccount->TwoFA_active) || false === $smarty.session.AdminAccount->TwoFA_active }  {* added for 2FA *}
                            <p class="text-muted">{__('loginLong')}</p>
                        {else}
                        {/if}
                    {/if}

                    <form method="post" action="index.php" class="form-horizontal" role="form">
                        {$jtl_token}
                        <input id="benutzer" type="hidden" name="adminlogin" value="1" />
                        {if isset($uri) && $uri|strlen > 0}
                            <input type="hidden" name="uri" value="{$uri}" />
                        {/if}
                        {if isset($smarty.session.AdminAccount->TwoFA_active) && true === $smarty.session.AdminAccount->TwoFA_active }  {* added for 2FA *}
                            <input type="hidden" name="benutzer" value="">
                            <input type="hidden" name="passwort" value="">
                            <p class="text-muted">{__('TwoFALogin')}</p>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input class="form-control" type="text" placeholder="2fa-code" name="TwoFA_code" id="inputTwoFA" value="" size="20" tabindex="10" />
                                <!-- <div id="counterbar" style="width:5px; background:lightgreen; position:absolute; left:250px; top:8px;"></div> -->
                                <div style="clear:both;"></div>
                                <div id="cb" style="width:218px;height:1px;background:red"></div>
                            </div>

                            {literal}
                                <script>
                                    $(document).ready(function () {
                                        $("[id$=inputTwoFA]").focus();
                                        var distance = (218 / 30);
                                        // "eye-candy" .. make a bar smaller every second, from a length of 30(s)
                                        var date = new Date();
                                        var sec = date.getSeconds();
                                        setInterval(function () {
                                            sec++;

                                            /* variant 1: vertical, shrinking bar right-side of the code-box */
                                            var len = sec;
                                            if(len < 30) {
                                                $("[id$=counterbar]").css('height', (30-len) );
                                            } else {
                                                $("[id$=counterbar]").css('height', (30-(len-30)) );
                                            }

                                            /* variant 2: horizontal, shrinking bar below the code-box */
                                            var d = (sec * distance);
                                            if(len < 30) {
                                                $("[id$=cb]").animate({width:Math.round((218-d))+'px'},900,'linear');
                                            } else {
                                                $("[id$=cb]").animate({width:Math.round((218-(d-218)))+'px'},900,'linear');
                                            }


                                            if (sec === 60) {
                                                sec = 0;
                                            }
                                        }, 1000);

                                    });

                                    function switchUser() {
                                        window.location.href = 'logout.php?token=' + $("[name$=jtl_token]").val();
                                    }
                                </script>
                            {/literal}
                        {else}
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-user"></i></span></div>
                                <input class="form-control" type="text" placeholder="{__('username')}" name="benutzer" id="user_login" value="" size="20" tabindex="10" />
                            </div>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-lock"></i></span></div>
                                <input class="form-control" type="password" placeholder="{__('password')}" name="passwort" id="user_pass" value="" size="20" tabindex="20" />
                            </div>
                            {if isset($code_adminlogin) && $code_adminlogin}
                                {captchaMarkup getBody=true}
                            {/if}
                        {/if}
                        <button type="submit" value="Anmelden" tabindex="100" class="btn btn-primary btn-block btn-md">{__('login')}</button>
                        {if isset($smarty.session.AdminAccount->TwoFA_active) && true === $smarty.session.AdminAccount->TwoFA_active }
                            <button type="button" tabindex="110" class="btn btn-default btn-block btn-md" onclick="switchUser();">{__('changerUser')}</button>
                        {/if}
                    </form>
                </div>
            </div>
            <p class="forgot-pw-wrap">
                <a href="pass.php" title="{__('forgotPassword')}"><i class="fa fa-lock"></i> {__('forgotPassword')}</a>
            </p>
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
