{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="systemlog"}
{include file='tpl_inc/seite_header.tpl' cTitel=__("systemlog") cBeschreibung=__("systemlogDesc") cDokuURL=__("systemlogURL")}

{assign var='cTab' value=$cTab|default:'log'}

<ul class="nav nav-tabs" role="tablist">
    <li role="presentation"{if $cTab === 'log'} class="active"{/if}>
        <a data-toggle="tab" role="tab" href="#log">{__("systemlogLog")}</a>
    </li>
    <li role="presentation"{if $cTab === 'config'} class="active"{/if}>
        <a data-toggle="tab" role="tab" href="#config">{__("systemlogConfig")}</a>
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
                    <div class="alert alert-info" role="alert">{__("noDataAvailable")}</div>
                {elseif $oLog_arr|@count === 0}
                    <div class="alert alert-info" role="alert">{__("noFilterResults")}</div>
                {else}
                    <div class="listgroup">
                        <div class="list-group-item">
                            <label>
                                <input type="checkbox" name="aaa" value="bbb"
                                       onchange="selectAllItems(this, $(this).prop('checked'))">
                                {__("selectAllShown")}
                            </label>
                        </div>
                        {foreach $oLog_arr as $oLog}
                            <div class="list-group-item">
                                <div class="row">
                                    <div class="col-md-3 col-xs-12">
                                        <label>
                                            <input type="checkbox" name="selected[]" value="{$oLog->kLog}">
                                            {if $oLog->nLevel >= $smarty.const.JTLLOG_LEVEL_ERROR}
                                                <span class="label label-danger">{__("systemlogError")}</span>
                                            {elseif $oLog->nLevel >= $smarty.const.JTLLOG_LEVEL_WARNING}
                                                <span class="label label-warning">{__("systemlogWarning")}</span>
                                            {elseif $oLog->nLevel >= $smarty.const.JTLLOG_LEVEL_INFO}
                                                <span class="label label-success">{__("systemlogNotice")}</span>
                                            {else}
                                                <span class="label label-info info">{__("systemlogDebug")}</span>
                                            {/if}
                                            {$oLog->dErstellt|date_format:"d.m.Y - H:i:s"}
                                        </label>
                                    </div>
                                    <div class="col-md-9 col-xs-12">
                                        <pre class="logtext
                                            {if $oLog->nLevel >= $smarty.const.JTLLOG_LEVEL_WARNING}bg-danger
                                            {elseif $oLog->nLevel >= $smarty.const.JTLLOG_LEVEL_INFO}bg-success
                                            {else}bg-info{/if}">{$oLog->cLog}</pre>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                        <div class="list-group-item">
                            <label>
                                <input type="checkbox" name="aaa" value="bbb"
                                       onchange="selectAllItems(this, $(this).prop('checked'))">
                                {__("selectAllShown")}
                            </label>
                        </div>
                    </div>
                {/if}
                <div class="panel-footer">
                    <div class="btn-group">
                        <button name="action" value="clearsyslog" class="btn btn-danger">
                            <i class="fa fa-trash"></i> {__("systemlogReset")}
                        </button>
                        <button name="action" value="delselected" class="btn btn-warning">
                            <i class="fa fa-trash"></i> {__("deleteSelected")}
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
                <h3 class="panel-title">{__("systemlogLevel")}</h3>
            </div>
            <div class="panel-body">
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="minLogLevel">Min. Loglevel</label>
                    </span>
                    <span class="input-group-wrap">
                        <select name="minLogLevel" id="minLogLevel" class="form-control combo">
                            <option{if $minLogLevel === $smarty.const.JTLLOG_LEVEL_EMERGENCY} selected{/if} value="{$smarty.const.JTLLOG_LEVEL_EMERGENCY}">Emergency</option>
                            <option{if $minLogLevel === $smarty.const.JTLLOG_LEVEL_ALERT} selected{/if} value="{$smarty.const.JTLLOG_LEVEL_ALERT}">Alert</option>
                            <option{if $minLogLevel === $smarty.const.JTLLOG_LEVEL_CRITICAL} selected{/if} value="{$smarty.const.JTLLOG_LEVEL_CRITICAL}">Critical</option>
                            <option{if $minLogLevel === $smarty.const.JTLLOG_LEVEL_ERROR} selected{/if} value="{$smarty.const.JTLLOG_LEVEL_ERROR}">Error</option>
                            <option{if $minLogLevel === $smarty.const.JTLLOG_LEVEL_WARNING} selected{/if} value="{$smarty.const.JTLLOG_LEVEL_WARNING}">Warning</option>
                            <option{if $minLogLevel === $smarty.const.JTLLOG_LEVEL_NOTICE} selected{/if} value="{$smarty.const.JTLLOG_LEVEL_NOTICE}">Notice</option>
                            <option{if $minLogLevel === $smarty.const.JTLLOG_LEVEL_INFO} selected{/if} value="{$smarty.const.JTLLOG_LEVEL_INFO}">Info</option>
                            <option{if $minLogLevel === $smarty.const.JTLLOG_LEVEL_DEBUG} selected{/if} value="{$smarty.const.JTLLOG_LEVEL_DEBUG}">Debug</option>
                        </select>
                    </span>
                </div>
            </div>
            <div class="panel-footer">
                <div class="btn-group">
                    <button name="action" value="save" class="btn btn-primary">
                        <i class="fa fa-save"></i> {__("save")}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}