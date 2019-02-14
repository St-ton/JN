{if empty($smarty.session.Kunde->kKunde)}
    {collapse id="nav-account-collapse" tag="div"  data=["parent"=>"#evo-main-nav-wrapper"] class="mt-2 pt-2 pb-0"}
        {form action="{get_static_route id='jtl.php' secure=true}" method="post" class="evo-validate px-5 py-3"}
        {$jtl_token}
            <fieldset id="quick-login">
                <div class="form-group">
                    <input type="email" name="email" id="email_quick" class="form-control"
                           placeholder="{lang key='emailadress'}" required
                           autocomplete="quick-login-email"/>
                </div>
                <div class="form-group">
                    <input type="password" name="passwort" id="password_quick" class="form-control"
                           required placeholder="{lang key='password'}"
                           autocomplete="quick-login-password"/>
                </div>
                {if isset($showLoginCaptcha) && $showLoginCaptcha}
                    <div class="form-group text-center">
                        {captchaMarkup getBody=true}
                    </div>
                {/if}
                <div class="form-group">
                    <input type="hidden" name="login" value="1"/>
                    {if !empty($oRedirect->cURL)}
                        {foreach $oRedirect->oParameter_arr as $oParameter}
                            <input type="hidden" name="{$oParameter->Name}" value="{$oParameter->Wert}" />
                        {/foreach}
                        <input type="hidden" name="r" value="{$oRedirect->nRedirect} "/>
                        <input type="hidden" name="cURL" value="{$oRedirect->cURL}" />
                    {/if}
                    <button type="submit" id="submit-btn" class="btn btn-primary btn-block">{lang key='login'}</button>
                </div>
            </fieldset>
        {/form}
        {dropdownitem href="{get_static_route id='pass.php'}" rel="nofollow" title="{lang key='forgotPassword'}" class="px-5"}
            {lang key='forgotPassword'}
        {/dropdownitem}
        {dropdownitem href="{get_static_route id='registrieren.php'}" rel="nofollow" title="{lang key='registerNow'}" class="px-5 pb-2 bg-info"}
            {lang key='newHere'} {lang key='registerNow'}
        {/dropdownitem}
    {/collapse}
{else}
    {collapse id="nav-account-collapse" tag="div"  data=["parent"=>"#evo-main-nav-wrapper"] class="mt-2 text-center"}
        {dropdownitem href="{get_static_route id='jtl.php' secure=true}" rel="nofollow" title="{lang key='myAccount'}"}
            {lang key='myAccount'}
        {/dropdownitem}
        {dropdownitem href="{get_static_route id='jtl.php' secure=true}?logout=1" rel="nofollow" title="{lang key='logOut'}"}
            {lang key='logOut'}
        {/dropdownitem}
    {/collapse}
{/if}