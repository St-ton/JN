{if is_object($oDuk)}
    <p class="duk">{$oDuk->cText}</p>
{else}
    <div class="widget-container"><div class="alert alert-info error">{__('noDataAvailable')}</div></div>
{/if}
