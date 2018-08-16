{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<div class="form-group float-label-control">
    <label for="email" class="control-label">{lang key='email' section='account data'}</label>
    <input type="text" name="email" id="login_email" class="form-control" placeholder="{lang key='email' section='account data'}" required autocomplete="email"/>
</div>
<div class="form-group float-label-control">
    <label for="password" class="control-label">{lang key='password' section='account data'}</label>
    <input type="password" name="passwort" id="login_password" class="form-control" placeholder="{lang key='password' section='account data'}" required autocomplete="current-password"/>
    <a class="small" href="{get_static_route id='pass.php'}"><span class="fa fa-question-circle"></span> {lang key='forgotPassword' section='global'}</a>
</div>

{if isset($showLoginCaptcha) && $showLoginCaptcha}
    <div class="form-group text-center float-label-control">
        {captchaMarkup getBody=true}
    </div>
{/if}

<div class="form-group">
    <input type="hidden" name="login" value="1" />
    {if !empty($oRedirect->cURL)}
        {foreach $oRedirect->oParameter_arr as $oParameter}
            <input type="hidden" name="{$oParameter->Name}" value="{$oParameter->Wert}" />
        {/foreach}
        <input type="hidden" name="r" value="{$oRedirect->nRedirect}" />
        <input type="hidden" name="cURL" value="{$oRedirect->cURL}" />
    {/if}
    <input type="submit" value="{lang key='login' section='checkout'}" class="btn btn-primary {if !isset($withSidebar) || $withSidebar === 0}btn-block{/if} btn-lg submit" />
</div>
