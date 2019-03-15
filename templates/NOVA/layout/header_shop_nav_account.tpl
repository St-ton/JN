{if empty($smarty.session.Kunde->kKunde)}
    {collapse id="nav-account-collapse" tag="div"  data=["parent"=>"#evo-main-nav-wrapper"] class="mt-md-2 py-0"}
        {form action="{get_static_route id='jtl.php' secure=true}" method="post" class="evo-validate px-5 pt-5 pb-3"}
            <fieldset id="quick-login">
                {formgroup}
                    {input type="email" name="email" id="email_quick"
                           placeholder="{lang key='emailadress'}" required=true
                           autocomplete="quick-login-email"}
                {/formgroup}
                {formgroup}
                    {input type="password" name="passwort" id="password_quick"
                           required=true placeholder="{lang key='password'}"
                           autocomplete="quick-login-password"}
                {/formgroup}
                {if isset($showLoginCaptcha) && $showLoginCaptcha}
                    {formgroup class="text-center"}
                        {captchaMarkup getBody=true}
                    {/formgroup}
                {/if}
                {formgroup class="mb-0"}
                    {input type="hidden" name="login" value="1"}
                    {if !empty($oRedirect->cURL)}
                        {foreach $oRedirect->oParameter_arr as $oParameter}
                            {input type="hidden" name=$oParameter->Name value=$oParameter->Wert}
                        {/foreach}
                        {input type="hidden" name="r" value=$oRedirect->nRedirect}
                        {input type="hidden" name="cURL" value=$oRedirect->cURL}
                    {/if}
                    {button type="submit" id="submit-btn" block=true variant="primary"}{lang key='login'}{/button}
                {/formgroup}
            </fieldset>
        {/form}
        {link href="{get_static_route id='pass.php'}" rel="nofollow" title="{lang key='forgotPassword'}" class="d-block px-5 pt-0 pb-2"}
            {lang key='forgotPassword'}
        {/link}
        {link href="{get_static_route id='registrieren.php'}" rel="nofollow" title="{lang key='registerNow'}" class="d-block px-5 py-3 bg-info"}
            {lang key='newHere'} <span class="text-decoration-underline">{lang key='registerNow'}</span>
        {/link}
    {/collapse}
{else}
    {collapse id="nav-account-collapse" tag="div"  data=["parent"=>"#evo-main-nav-wrapper"] class="mt-md-2 text-center"}
        {dropdownitem href="{get_static_route id='jtl.php' secure=true}" rel="nofollow" title="{lang key='myAccount'}"}
            {lang key='myAccount'}
        {/dropdownitem}
        {dropdownitem href="{get_static_route id='jtl.php' secure=true}?bestellungen=1" rel="nofollow" title="{lang key='myAccount'}"}
            {lang key='myOrders'}
        {/dropdownitem}
        {dropdownitem href="{get_static_route id='jtl.php' secure=true}?editRechnungsadresse=1" rel="nofollow" title="{lang key='myAccount'}"}
            {lang key='myPersonalData'}
        {/dropdownitem}
        {dropdownitem href="{get_static_route id='jtl.php' secure=true}#my-wishlists" rel="nofollow" title="{lang key='myAccount'}"}
            {lang key='myWishlists'}
        {/dropdownitem}
        {dropdownitem href="{get_static_route id='jtl.php' secure=true}?logout=1" rel="nofollow" title="{lang key='logOut'}"}
            {lang key='logOut'}
        {/dropdownitem}
    {/collapse}
{/if}