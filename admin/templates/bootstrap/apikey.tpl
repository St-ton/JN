{include file='tpl_inc/header.tpl'}

{if $step === 'overview'}
    {include file='tpl_inc/model_tabs.tpl' items=$models select=true create=true edit=true search=true delete=true disable=false enable=false}
{elseif $step === 'detail'}
    {include file='tpl_inc/model_detail.tpl' item=$item saveAndContinue=true save=true cancel=true}
{/if}

{include file='tpl_inc/footer.tpl'}
