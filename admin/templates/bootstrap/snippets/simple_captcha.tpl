<input type="hidden" name="md5" value="{$captchaCodemd5}" id="captcha_md5">
<div class="captcha">
    <img src="{$captchaCodeURL}" alt="{#code#}" id="captcha" />
</div>
<a href="index.php" class="captcha">{#reloadCaptcha#}</a>
<p>
    <input class="form-control" type="text" name="captcha" tabindex="30" id="captcha_text" placeholder="{#enterCode#}" />
</p>
{if isset($bAnti_spam_failed) && $bAnti_spam_failed}
    <div class="form-error-msg text-danger"><i class="fa fa-warning"></i>
        {#invalidToken#}
    </div>
{/if}