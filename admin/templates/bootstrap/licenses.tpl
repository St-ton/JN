{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('My purchases') cBeschreibung=__('pageDesc') cDokuURL=__('https://www.jtl-software.de')}

<div id="content">
    <div id="error-placeholder" class="alert alert-danger d-none"></div>
    {include file='tpl_inc/licenses_store_connection.tpl'}
    {if $hasAuth}
        {include file='tpl_inc/licenses_bound.tpl' licenses=$licenses}
        {include file='tpl_inc/licenses_unbound.tpl' licenses=$licenses}
        {if isset($smarty.get.debug)}
            <h3>AuthToken</h3>
            <pre>{$authToken}</pre>
            <h3>License data</h3>
            <pre>{$licenses|var_dump}</pre>
            <h3>Raw data</h3>
            <pre>{$rawData|var_dump}</pre>
        {/if}
    {/if}
</div>

{include file='tpl_inc/licenses_scripts.tpl'}
{include file='tpl_inc/footer.tpl'}
