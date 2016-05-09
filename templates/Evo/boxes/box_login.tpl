<section class="panel panel-default box box-login" id="sidebox{$oBox->kBox}">
    <div class="panel-heading">
        <h5 class="panel-title">{if !isset($smarty.session.Kunde) || $smarty.session.Kunde->kKunde == 0}{lang key="login" section="global"}{else}{lang key="hello" section="global"}, {$smarty.session.Kunde->cVorname} {$smarty.session.Kunde->cNachname}{/if}</h5>
    </div>
    <div class="panel-body">
        {if empty($smarty.session.Kunde->kKunde)}
            <form action="{$ShopURLSSL}/jtl.php" method="post" class="form box_login">
                <input type="hidden" name="login" value="1" />
                {$jtl_token}
                <div class="form-group required">
                    <label for="email" class="control-label">{lang key="emailadress" section="global"}</label>
                    <input type="text" name="email" id="email" class="form-control" placeholder="{lang key="emailadress" section="global"}" required />
                </div>
                <div class="form-group required">
                    <label for="password" class="control-label">{lang key="password" section="account data"}</label>
                    <input type="password" name="passwort" id="password" class="form-control" placeholder="{lang key="password" section="account data"}" required />
                </div>

                {if isset($showLoginCaptcha) && $showLoginCaptcha}
                    {*@todo: remove/use reCaptcha*}
                    <div class="form-group text-center float-label-control">
                        <img src="{$code_login->codeURL}" alt="Captcha" />
                        <input type="text" name="code_login" id="code_login" class="form-control" placeholder="{lang key="code" section="global"}*" />
                    </div>
                {/if}

                <div class="form-group">
                    {if !empty($oRedirect->cURL)}
                        {foreach name=parameter from=$oRedirect->oParameter_arr item=oParameter}
                            <input type="hidden" name="{$oParameter->Name}" value="{$oParameter->Wert}" />
                        {/foreach}
                        <input type="hidden" name="r" value="{$oRedirect->nRedirect}" />
                        <input type="hidden" name="cURL" value="{$oRedirect->cURL}" />
                    {/if}
                    <input type="submit" value="{lang key="login" section="checkout"}" class="btn btn-primary btn-block submit" />
                </div>
                <ul class="register-or-resetpw nav">
                    <li>
                        <a class="resetpw pull-left btn-block" href="pass.php">
                            <span class="fa fa-question-circle"></span> {lang key="forgotPassword" section="global"}
                        </a>
                    </li>
                    <li>
                        <a class="register pull-left btn-block" href="registrieren.php">
                            <span class="fa fa-pencil"></span> {lang key="newHere" section="global"} {lang key="registerNow" section="global"}
                        </a>
                    </li>
                </ul>
            </form>
        {else}
            <a href="jtl.php" class="btn btn-default btn-block btn-sm btn-account">{lang key="myAccount" section="global"}</a>
            <a href="jtl.php?logout=1&token={$smarty.session.jtl_token}" class="btn btn-block btn-sm btn-warning btn-logout">{lang key="logOut" section="global"}</a>
        {/if}
    </div>
</section>