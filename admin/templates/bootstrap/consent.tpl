{include file='tpl_inc/header.tpl'}

<style>#sidebar { display: none; } </style>

{if $step === 'overview'}
    {include file='tpl_inc/model_list.tpl' items=$consents select=true edit=true search=true delete=true activate=true save=true enable=true disable=true}
{elseif $step === 'detail'}
    {include file='tpl_inc/model_detail.tpl' item=$item select=true edit=true search=true delete=true activate=true save=true enable=true disable=true}
{/if}

{include file='tpl_inc/footer.tpl'}
