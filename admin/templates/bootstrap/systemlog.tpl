{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="systemlog"}
{include file='tpl_inc/seite_header.tpl' cTitel=#systemlog# cBeschreibung=#systemlogDesc# cDokuURL=#systemlogURL#}

{assign var='cTab' value=$cTab|default:'log'}

<ul class="nav nav-tabs" role="tablist">
    <li role="presentation"{if $cTab === 'log'} class="active"{/if}>
        <a data-toggle="tab" role="tab" href="#log">{#systemlogLog#}</a>
    </li>
    <li role="presentation"{if $cTab === 'config'} class="active"{/if}>
        <a data-toggle="tab" role="tab" href="#config">{#systemlogConfig#}</a>
    </li>
</ul>

<div class="tab-content">
    <div role="tabpanel" class="tab-pane fade{if $cTab === 'log'} active in{/if}" id="log">
        {if $nTotalLogCount !== 0}
            {include file='tpl_inc/filtertools.tpl' oFilter=$oFilter}
            {include file='tpl_inc/pagination.tpl' oPagination=$oPagination}
        {/if}

        <div class="panel panel-default">
            <form method="post" action="systemlog.php">
                {$jtl_token}
                {if $nTotalLogCount === 0}
                    <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                {elseif $oLog_arr|@count === 0}
                    <div class="alert alert-info" role="alert">{#noFilterResults#}</div>
                {else}
                    <div class="listgroup">
                        <div class="list-group-item">
                            <label>
                                <input type="checkbox" name="aaa" value="bbb"
                                       onchange="selectAllItems(this, $(this).prop('checked'))">
                                {#selectAllShown#}
                            </label>
                        </div>
                        {foreach $oLog_arr as $oLog}
                            <div class="list-group-item">
                                <div class="row">
                                    <div class="col-md-3 col-xs-12">
                                        <label>
                                            <input type="checkbox" name="selected[]" value="{$oLog->kLog}">
                                            {if $oLog->nLevel == 1 || $oLog->nLevel >= $smarty.const.JTLLOG_LEVEL_ERROR}
                                                <span class="label label-danger">{#systemlogError#}</span>
                                            {elseif $oLog->nLevel == 2 || $oLog->nLevel >= $smarty.const.JTLLOG_LEVEL_INFO}
                                                <span class="label label-success">{#systemlogNotice#}</span>
                                            {elseif $oLog->nLevel == 4 || $oLog->nLevel >= $smarty.const.JTLLOG_LEVEL_DEBUG}
                                                <span class="label label-info info">{#systemlogDebug#}</span>
                                            {/if}
                                            {$oLog->dErstellt|date_format:"d.m.Y - H:i:s"}
                                        </label>
                                    </div>
                                    <div class="col-md-9 col-xs-12">
                                        <pre class="logtext
                                            {if $oLog->nLevel == 1 || $oLog->nLevel >= $smarty.const.JTLLOG_LEVEL_ERROR}bg-danger
                                            {elseif $oLog->nLevel == 2 || $oLog->nLevel >= $smarty.const.JTLLOG_LEVEL_INFO}bg-success
                                            {elseif $oLog->nLevel == 4 || $oLog->nLevel >= $smarty.const.JTLLOG_LEVEL_DEBUG}bg-info{/if}">{$oLog->cLog}</pre>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                        <div class="list-group-item">
                            <label>
                                <input type="checkbox" name="aaa" value="bbb"
                                       onchange="selectAllItems(this, $(this).prop('checked'))">
                                {#selectAllShown#}
                            </label>
                        </div>
                    </div>
                {/if}
                <div class="panel-footer">
                    <div class="btn-group">
                        <button name="action" value="clearsyslog" class="btn btn-danger">
                            <i class="fa fa-trash"></i> {#systemlogReset#}
                        </button>
                        <button name="action" value="delselected" class="btn btn-warning">
                            <i class="fa fa-trash"></i> {#deleteSelected#}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div role="tabpanel" class="tab-pane fade{if $cTab === 'config'} active in{/if}" id="config">
        <form class="panel panel-default settings" action="systemlog.php" method="post">
            {$jtl_token}
            <div class="panel-heading">
                <h3 class="panel-title">{#systemlogLevel#}</h3>
            </div>
            <div class="panel-body">
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="JTLLOG_LEVEL_ERROR">{#systemlogError#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="checkbox" name="nLevelFlags[]" value="{$JTLLOG_LEVEL_ERROR}"
                               id="JTLLOG_LEVEL_ERROR" {if $nLevelFlag_arr[$JTLLOG_LEVEL_ERROR] != 0}checked{/if}>
                    </span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="JTLLOG_LEVEL_NOTICE">{#systemlogNotice#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="checkbox" name="nLevelFlags[]" value="{$JTLLOG_LEVEL_NOTICE}"
                               id="JTLLOG_LEVEL_NOTICE" {if $nLevelFlag_arr[$JTLLOG_LEVEL_NOTICE] != 0}checked{/if}>
                    </span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="JTLLOG_LEVEL_DEBUG">{#systemlogDebug#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="checkbox" name="nLevelFlags[]" value="{$JTLLOG_LEVEL_DEBUG}"
                               id="JTLLOG_LEVEL_DEBUG" {if $nLevelFlag_arr[$JTLLOG_LEVEL_DEBUG] != 0}checked{/if}>
                    </span>
                </div>
            </div>
            <div class="panel-footer">
                <div class="btn-group">
                    <button name="action" value="save" class="btn btn-primary">
                        <i class="fa fa-save"></i> {#save#}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}