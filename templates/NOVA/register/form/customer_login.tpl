{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{formgroup label="{lang key='email' section='account data'}" label-for="login_email"}
    {input type="text" name="email" id="login_email" placeholder="{lang key='email' section='account data'}" required=true autocomplete="email"}
{/formgroup}
{formgroup label="{lang key='password' section='account data'}" label-for="login_password"}
    {input type="password" name="passwort" id="login_password" placeholder="{lang key='password' section='account data'}" required=true autocomplete="current-password"}
{/formgroup}

{if isset($showLoginCaptcha) && $showLoginCaptcha}
    <div class="form-group text-center">
        {captchaMarkup getBody=true}
    </div>
{/if}

{formgroup}
    {input type="hidden" name="login" value="1"}
    {if !empty($oRedirect->cURL)}
        {foreach $oRedirect->oParameter_arr as $oParameter}
            {input type="hidden" name="{$oParameter->Name}" value="{$oParameter->Wert}"}
        {/foreach}
        {input type="hidden" name="r" value="{$oRedirect->nRedirect}"}
        {input type="hidden" name="cURL" value="{$oRedirect->cURL}"}
    {/if}
    {button type="submit" variant="primary" class="{if !isset($withSidebar) || $withSidebar === 0}btn-block{/if} submit"}
        {lang key='login' section='checkout'}
    {/button}
    {link class="small" href="{get_static_route id='pass.php'}" class="ml-3"}
        <span class="fa fa-question-circle"></span> {lang key='forgotPassword'}
    {/link}
{/formgroup}
