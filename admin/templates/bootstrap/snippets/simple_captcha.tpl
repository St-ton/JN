<input type="hidden" name="{$captchaToken}" value="{$captchaCode}">
<label class="mt-3">{lang key="captcha_code_active" section="global"}</label>
{if isset($bAnti_spam_failed) && $bAnti_spam_failed}
    <div class="form-error-msg text-danger"><i class="fal fa-exclamation-triangle"></i>
        {__('invalidToken')}
    </div>
{/if}