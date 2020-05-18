{block name='snippets-simple-captcha'}
    {input type="hidden" name=$captchaToken value=$captchaCode}
    {if isset($bAnti_spam_failed) && $bAnti_spam_failed}
        {block name='snippets-simple-captcha-msg'}
            <div class="form-error-msg text-danger">
                <i class="fas fa-exclamation-triangle"></i>
                {lang key='invalidToken'}
            </div>
        {/block}
    {/if}
{/block}
