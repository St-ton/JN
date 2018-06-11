<div class="g-recaptcha" data-sitekey="{$jtl_google_recaptcha_sitekey}" data-callback="g_recaptcha_filled"></div>
{if isset($bAnti_spam_failed) && $bAnti_spam_failed}
    <div class="form-error-msg text-danger"><i class="fa fa-warning"></i>
        {lang key="invalidToken" section="global"}
    </div>
{/if}