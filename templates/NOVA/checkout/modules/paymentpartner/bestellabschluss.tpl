<div style="margin:10px 0;">
    {if $status === 'error'}
        <strong>{$error}</strong>
    {else}
        {lang key='paymentpartnerDesc' section=''}
        {strip}
            <div>
                {link href="{$url}"}
                    {image src="{$currentTemplateDir}../../gfx/PaymentPartner/logo.png" alt="PaymentPartner Logo"}
                {/link}
            </div>
        {/strip}
    {/if}
</div>
