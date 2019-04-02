{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{formgroup labelFor=$captchaToken label="{lang key='captcha_code_active'}"}
    {input type="hidden" name=$captchaToken value=$captchaCode}
    {if isset($bAnti_spam_failed) && $bAnti_spam_failed}
        <div class="form-error-msg text-danger"><i class="fas fa-exclamation-triangle"></i>
            {lang key='invalidToken'}
        </div>
    {/if}
{/formgroup}