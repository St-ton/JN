{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('My purchases') cBeschreibung=__('pageDesc') cDokuURL=__('https://www.jtl-software.de')}

<div id="content">
    {include file='tpl_inc/licenses_store_connection.tpl'}
    {if $hasAuth}
        {include file='tpl_inc/licenses_bound.tpl' licenses=$licenses}
        {include file='tpl_inc/licenses_unbound.tpl' licenses=$licenses}
    {/if}
</div>

{include file='tpl_inc/licenses_scripts.tpl'}
{include file='tpl_inc/footer.tpl'}
