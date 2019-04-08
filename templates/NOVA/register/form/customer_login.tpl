{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='register-form-customer-login'}
    {block name='register-form-customer-login-email'}
        {formgroup label="{lang key='email' section='account data'}" label-for="login_email"}
            {input type="text" name="email" id="login_email" placeholder="{lang key='email' section='account data'}" required=true autocomplete="email"}
        {/formgroup}
    {/block}
    {block name='register-form-customer-login-password'}
        {formgroup label="{lang key='password' section='account data'}" label-for="login_password"}
            {input type="password" name="passwort" id="login_password" placeholder="{lang key='password' section='account data'}" required=true autocomplete="current-password"}
        {/formgroup}
    {/block}
    {if isset($showLoginCaptcha) && $showLoginCaptcha}
        {block name='register-form-customer-login-captcha'}
            <div class="form-group text-center">
                {captchaMarkup getBody=true}
            </div>
        {/block}
    {/if}

    {block name='register-form-customer-login-submit'}
        {formgroup}
            {input type="hidden" name="login" value="1"}
            {if !empty($oRedirect->cURL)}
                {foreach $oRedirect->oParameter_arr as $oParameter}
                    {input type="hidden" name=$oParameter->Name value=$oParameter->Wert}
                {/foreach}
                {input type="hidden" name="r" value=$oRedirect->nRedirect}
                {input type="hidden" name="cURL" value=$oRedirect->cURL}
            {/if}
            {button type="submit" variant="primary" block=(!isset($withSidebar) || $withSidebar === 0)}
                {lang key='login' section='checkout'}
            {/button}
            {link class="small" href="{get_static_route id='pass.php'}" class="ml-3"}
                <span class="fa fa-question-circle"></span> {lang key='forgotPassword'}
            {/link}
        {/formgroup}
    {/block}
{/block}
