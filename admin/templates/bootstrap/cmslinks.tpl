{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='cmslinks'}
{include file='tpl_inc/seite_header.tpl' cTitel=#cmsLinks# cBeschreibung=#cmsLinksDesc# cDokuURL=#cmsLinksUrl#}

{$links|@var_dump}

{if $links|@count > 0 && $links}
    {include file='tpl_inc/pagination.tpl' oPagination=$oPagiCmsLinks cAnchor='cmsLinks'}
    <div class="panel panel-default">
        {$jtl_token}
        <input type="hidden" name="cmsLinks" value="1" />
        <div class="table-responsive">
            <table class="list table table-hover">
                <thead>
                <tr>
                    <th class="tleft">interne ID</th>
                    <th class="tleft">last modified</th>
                </tr>
                </thead>
                <tbody>
                {foreach name=cmsLinks from=$links item=oLink}
                    <tr>
                        <td>{$oLink->kPage}</td>
                        <td>{$oLink->dLastModified}</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{else}
    <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
{/if}


{include file='tpl_inc/footer.tpl'}