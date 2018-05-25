<input type="hidden" name="md5" value="{$captchaCodemd5}" id="captcha_md5">
<div class="captcha">
    <img src="{$captchaCodeURL}" alt="{lang key="captcha_enter_code"}" id="captcha" />
</div>
<input class="form-control" type="text" name="captcha" tabindex="30" id="captcha_text" placeholder="{lang key="captcha_enter_code"}" required="required" />
{if isset($bAnti_spam_failed) && $bAnti_spam_failed}
    <div class="form-error-msg text-danger"><i class="fa fa-warning"></i>
        {lang key="invalidToken" section="global"}
    </div>
{/if}
