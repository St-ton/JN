{include file='layout/header.tpl'}

{if $step === 'umfrage_uebersicht'}
    {include file='poll/overview.tpl'}
{elseif $step === 'umfrage_durchfuehren'}
    {include file='poll/progress.tpl'}
{elseif $step === 'umfrage_ergebnis'}
    {include file='poll/result.tpl'}
{/if}

{include file='layout/footer.tpl'}