<div class="form-group float-label-control required">
    <input type="hidden" name="{$captchaToken}" value="{$captchaCode}">
    <label>{lang key='captcha_code_active' section='global'}</label>
    {if isset($bAnti_spam_failed) && $bAnti_spam_failed}
        <div class="form-error-msg text-danger"><i class="fa fa-warning"></i>
            {lang key='invalidToken' section='global'}
        </div>
    {/if}
</div>
