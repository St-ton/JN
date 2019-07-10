{include file='tpl_inc/seite_header.tpl' cTitel=__('cache') cBeschreibung=__('objectcacheDesc') cDokuURL=__('cacheURL')}
<script type="text/javascript">
    var disabledMethods      = {$non_available_methods},
        disFunctionalMethods = {$disfunctional_methods};
    jQuery(document).ready(function ($) {ldelim}
        var elem,
            methods = $('#caching_method option');
        if (methods) {ldelim}
            methods.each(function () {ldelim}
                elem = $(this);
                if (disabledMethods.indexOf(elem.val()) >= 0) {ldelim}
                    elem.attr('disabled', 'disabled');
                {rdelim} else if (disFunctionalMethods.indexOf(elem.val()) >= 0) {ldelim}
                    elem.text(elem.text() + ' {__('configurationError')}');
                {rdelim}
            {rdelim});
        {rdelim}
        $('#massaction-main-switch').on('click', function () {ldelim}
            var checkboxes = $('.massaction-checkbox'),
                checked = $(this).prop('checked');
            checkboxes.prop('checked', checked);
        {rdelim});

        $('#btn_toggle_cache').on('click', function () {ldelim}
            $("#row_toggle_cache").slideToggle('slow', 'linear');
        {rdelim});
    {rdelim});
</script>
<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($tab) || $tab === 'massaction' || $tab === 'uebersicht'} active{/if}">
            <a data-toggle="tab" role="tab" href="#massaction">{__('management')}</a>
        </li>
        <li class="tab{if isset($tab) && $tab === 'stats'} active{/if}">
            <a data-toggle="tab" role="tab" href="#stats">{__('stats')}</a>
        </li>
        <li class="tab{if isset($tab) && $tab === 'benchmark'} active{/if}">
            <a data-toggle="tab" role="tab" href="#benchmark">{__('benchmark')}</a>
        </li>
        <li class="tab{if isset($tab) && $tab === 'settings'} active{/if}">
            <a data-toggle="tab" role="tab" href="#settings">{__('settings')}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="massaction" class="tab-pane fade {if !isset($tab) || $tab === 'massaction' || $tab === 'uebersicht'} active in{/if}">
            <form method="post" action="cache.php">
                {$jtl_token}
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">{__('management')}</div>
                    </div>
                    <div class="table-responsive">
                    <table id="cache-type-status" class="table list">
                        <thead>
                        <tr>
                            <th class="tleft">
                                <input type="checkbox" class="massaction-checkbox" id="massaction-main-switch" />
                            </th>
                            <th class="tleft"><label style="margin-bottom:0;" for="massaction-main-switch">{__('type')}</label></th>
                            <th class="tleft">{__('description')}</th>
                            <th class="tleft">{__('entries')}</th>
                            <th class="tleft">{__('status')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $caching_groups as $cg}
                            <tr class="{if ($cg@index % 2) === 0}even{else}odd{/if}">
                                <td>
                                    <input type="checkbox" class="massaction-checkbox" value="{$cg.value}" name="cache-types[]" id="group-cb-{$cg@index}">
                                </td>
                                <td>
                                    {assign var=nicename value=$cg.nicename}
                                    <label for="group-cb-{$cg@index}">{__($nicename)}</label>
                                </td>
                                <td>
                                    {assign var=description value=$cg.description}
                                    {__($description)}
                                </td>
                                <td>{$cg.key_count}</td>
                                <td>
                                    <h4 class="label-wrap">
                                        {if $cache_enabled === false || $cg.value|in_array:$disabled_caches}
                                            <span class="label label-danger inactive">{__('inactive')}</span>
                                        {else}
                                            <span class="label label-success active">{__('active')}</span>
                                        {/if}
                                    </h4>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                    </div>
                    <div class="card-footer">
                        <div class="input-container" style="max-width: 50%;">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="cache-action">{__('action')}</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select class="form-control" name="cache-action" id="cache-action">
                                        <option name="flush" value="flush">{__('empty')}</option>
                                        <option name="deaktivieren" value="deactivate">{__('deactivate')}</option>
                                        <option name="aktivieren" value="activate">{__('activate')}</option>
                                    </select>
                                </span>
                                <span class="input-group-btn">
                                    <button type="submit" value="{__('submit')}" class="btn btn-primary">{__('submit')}</button>
                                </span>
                            </div>
                            <input name="a" type="hidden" value="cacheMassAction" />
                        </div>
                        <div class="input-container">
                            <form method="post" action="cache.php" class="submit-form">
                                {$jtl_token}
                                <span class="submit_wrap btn-group">
                                    <button name="a" type="submit" value="flush_object_cache" class="btn btn-default delete"{if !$cache_enabled} disabled="disabled"{/if}><i class="fa fa-trash"></i>&nbsp;{__('clearObjectCache')}</button>
                                    <button name="a" type="submit" value="flush_template_cache" class="btn btn-default delete"><i class="fa fa-trash"></i>&nbsp;{__('clearTemplateCache')}</button>
                                </span>
                            </form>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </form>
        </div>
        <div id="stats" class="tab-pane fade {if isset($tab) && $tab === 'stats'} active in{/if}">
            {if is_array($stats) && $stats|@count > 0}
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">{__('objectcache')}</div>
                    </div>
                    <table class="table">
                        {if isset($stats.uptime_h) && $stats.uptime_h !== null}
                            <tr class="cache-row">
                                <td>{__('uptime')}:</td>
                                <td>{$stats.uptime_h}</td>
                            </tr>
                        {/if}
                        {if isset($stats.mem) && $stats.mem !== null}
                            <tr class="cache-row">
                                <td>{__('fullSize')}:</td>
                                <td>{$stats.mem} Bytes{if $stats.mem|strpos:'/' === false} ({($stats.mem/1024/1024)|string_format:'%.2f'} MB){/if}</td>
                            </tr>
                        {/if}
                        {if isset($stats.entries) && $stats.entries !== null}
                            <tr class="cache-row">
                                <td>{__('entriesCount')}:</td>
                                <td>{$stats.entries}</td>
                            </tr>
                        {/if}
                        {if isset($stats.misses) && $stats.misses !== null}
                            <tr class="cache-row">
                                <td>{__('misses')}:</td>
                                <td>{$stats.misses}
                                    {if isset($stats.mps) && $stats.mps !== null && $stats.mps|strpos:'/' === false}
                                        <span class="inline"> ({$stats.mps|string_format:'%.2f'} {__('misses')}/s)</span>
                                    {/if}
                                </td>
                            </tr>
                        {/if}
                        {if isset($stats.hits) && $stats.hits !== null}
                            <tr class="cache-row">
                                <td>Hits:</td>
                                <td>{$stats.hits}
                                    {if isset($stats.hps) && $stats.hps !== null && $stats.hps|strpos:'/' === false}
                                        <span class="inline"> ({$stats.hps|string_format:'%.2f'} {__('hits')}/s)</span>
                                    {/if}
                                </td>
                            </tr>
                        {/if}
                        {if isset($stats.inserts) && $stats.inserts !== null}
                            <tr class="cache-row">
                                <td>{__('inserts')}:</td>
                                <td>{$stats.inserts}</td>
                            </tr>
                        {/if}
                    </table>
                </div>
                {if isset($stats.slow) && is_array($stats.slow)}
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">{__('slowlog')}</div>
                        </div>
                        {if $stats.slow|@count > 0}
                            <table class="table">
                                {foreach $stats.slow as $slow}
                                    <tr>
                                        <td>{$slow.date}</td>
                                        <td>{$slow.cmd} ({$slow.exec_time}s)</td>
                                    </tr>
                                {/foreach}
                            </table>
                        {else}
                            <div class="card-body">
                                <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                            </div>
                        {/if}
                    </div>
                {/if}
            {else}
                <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
            {/if}
            {if $opcache_stats !== null}
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">OpCache</div>
                    </div>
                    <table class="table cache-stats" id="opcache-stats">
                        <tr class="cache-row">
                            <td>{__('activated')}:</td>
                            <td class="value">{if $opcache_stats->enabled === true}ja{else}nein{/if}</td>
                        </tr>
                        <tr class="cache-row">
                            <td>{__('busySpace')}:</td>
                            <td class="value">{$opcache_stats->memoryUsed} MB</td>
                        </tr>
                        <tr class="cache-row">
                            <td>{__('emptySpace')}:</td>
                            <td class="value">{$opcache_stats->memoryFree} MB</td>
                        </tr>
                        <tr class="cache-row">
                            <td>{__('scriptCountInCache')}:</td>
                            <td class="value">{$opcache_stats->numberScrips}</td>
                        </tr>
                        <tr class="cache-row">
                            <td>{__('keyCountInCache')}:</td>
                            <td class="value">{$opcache_stats->numberKeys}</td>
                        </tr>
                        <tr class="cache-row">
                            <td>{__('hits')}:</td>
                            <td class="value">{$opcache_stats->hits}</td>
                        </tr>
                        <tr class="cache-row">
                            <td>{__('misses')}:</td>
                            <td class="value">{$opcache_stats->misses}</td>
                        </tr>
                        <tr class="cache-row collapsed clickable" data-toggle="collapse" data-target="#hitRateDetail" style="cursor: pointer">
                            <td>{__('hitRate')}:</td>
                            <td class="value">{$opcache_stats->hitRate}%&nbsp;<i class="fa fa-info-circle right"></i></td>
                        </tr>
                        <tr class="cache-row">
                            <td colspan="2" style="padding: 0">
                                <div id="hitRateDetail" class=" collapse">
                                    <table class="table cache-stats">
                                        {foreach $opcache_stats->scripts as $script}
                                            <tr class="cache-row">
                                                <td class="file-path">{$script.full_path}</td>
                                                <td class="value">{$script.hits} Hits</td>
                                            </tr>
                                        {/foreach}
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            {/if}
            {if $tplcacheStats !== null}
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">{__('templateCache')}</div>
                    </div>
                    <table class="table cache-stats" id="tplcache-stats">
                        <tr class="cache-row collapsed clickable" data-toggle="collapse" data-target="#cachefilesFrontendDetail" style="cursor: pointer">
                            <td>{__('files')} {__('frontend')}</td>
                            <td class="value">{$tplcacheStats->frontend|count}&nbsp;<i class="fa fa-info-circle right"></i></td>
                        </tr>
                        {if $tplcacheStats->frontend|count > 0}
                        <tr class="cache-row">
                            <td colspan="2" style="padding: 0">
                                <div id="cachefilesFrontendDetail" class=" collapse">
                                    <table class="table cache-stats">
                                        {foreach $tplcacheStats->frontend as $file}
                                            <tr class="cache-row">
                                                <td class="file-path">{$file->fullname}</td>
                                            </tr>
                                        {/foreach}
                                    </table>
                                </div>
                            </td>
                        </tr>
                        {/if}
                        <tr class="cache-row collapsed clickable" data-toggle="collapse" data-target="#cachefilesBackendDetail" style="cursor: pointer">
                            <td>{__('files')} {__('backend')}</td>
                            <td class="value">{$tplcacheStats->backend|count}&nbsp;<i class="fa fa-info-circle right"></i></td>
                        </tr>
                        {if $tplcacheStats->backend|count > 0}
                        <tr class="cache-row">
                            <td colspan="2" style="padding: 0">
                                <div id="cachefilesBackendDetail" class=" collapse">
                                    <table class="table cache-stats">
                                        {foreach $tplcacheStats->backend as $file}
                                            <tr class="cache-row">
                                                <td class="file-path">{$file->fullname}</td>
                                            </tr>
                                        {/foreach}
                                    </table>
                                </div>
                            </td>
                        </tr>
                        {/if}
                    </table>
                </div>
            {/if}
        </div>
        <div id="benchmark" class="tab-pane fade {if isset($tab) && $tab === 'benchmark'} active in{/if}">
            {if !empty($all_methods) && $all_methods|@count > 0}
                <div class="card settings">
                    <div class="card-header">
                        <div class="card-title">{__('settings')}</div>
                    </div>
                    <form method="post" action="cache.php">
                        {$jtl_token}
                        <div class="card-body">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="runcount">{__('runs')}</label>
                                </span>
                                <input class="form-control" type="number" name="runcount" id="runcount" value="{if isset($smarty.post.runcount) && is_numeric($smarty.post.runcount)}{$smarty.post.runcount}{else}1000{/if}" size="5" />
                            </div>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="repeat">{__('repeats')}</label>
                                </span>
                                <input class="form-control" type="number" name="repeat" id="repeat" value="{if isset($smarty.post.repeat) && is_numeric($smarty.post.repeat)}{$smarty.post.repeat}{else}1{/if}" size="5" />
                            </div>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="testdata">{__('testData')}</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select class="form-control" name="testdata" id="testdata">
                                        <option value="array"{if isset($smarty.post.testdata) && $smarty.post.testdata === 'array'} selected{/if}>{__('typeArray')}</option>
                                        <option value="object"{if isset($smarty.post.testdata) && $smarty.post.testdata === 'object'} selected{/if}>{__('typeObject')}</option>
                                        <option value="string"{if isset($smarty.post.testdata) && $smarty.post.testdata === 'string'} selected{/if}>{__('typeString')}</option>
                                    </select>
                                </span>
                            </div>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="methods">{__('methods')}</label>
                                </span>
                                <select class="form-control" name="methods[]" id="methods" multiple>
                                    {foreach $all_methods as $method}
                                        <option value="{$method}"{if !empty($smarty.post.methods) && $method|in_array:$smarty.post.methods}selected{/if}>{$method}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <input name="a" type="hidden" value="benchmark" />
                        </div>
                        <div class="card-footer">
                            <button name="submit" type="submit" value="Benchmark starten" class="btn btn-primary">{__('startBenchmark')}</button>
                        </div>
                    </form>
                </div>
                {if isset($bench_results)}
                    {if is_array($bench_results)}
                        {foreach from=$bench_results key=resultsKey item=result}
                            {if isset($result.method)}
                                <div class="bench-result card" style="margin-top: 20px;">
                                    <div class="card-header">
                                        <div class="card-title">{$result.method}</div>
                                    </div>
                                    <div class="card-body">
                                    <p><span class="opt">{__('status')}: </span> <span class="label {if $result.status === 'ok'}label-success{else}label-danger{/if}">{$result.status}</span></p>
                                    <p><span class="opt">{__('time')} get: </span>
                                        {if $result.status !== 'failed' && $result.status !== 'invalid'}
                                            <span class="text">{$result.timings.get}s</span>
                                            <span class="text">({$result.rps.get} {__('entries')}/s)</span>
                                        {else}
                                            <span class="text">-</span>
                                        {/if}
                                    </p>

                                    <p><span class="opt">{__('time')} set: </span>
                                        {if $result.status !== 'failed' && $result.status !== 'invalid'}
                                            <span class="text">{$result.timings.set}s</span>
                                            <span class="text">({$result.rps.set} {__('entries')}/s)</span>
                                        {else}
                                            <span class="text">-</span>
                                        {/if}
                                    </p>
                                    </div>
                                </div>
                            {/if}
                        {/foreach}
                    {else}
                        <div class="alert alert-warning">{__('errorBenchmark')}</div>
                    {/if}
                {/if}
            {else}
                <div class="alert alert-warning">{__('errorMethodNotFound')}</div>
            {/if}
        </div>
        <div id="settings" class="tab-pane fade {if isset($tab) && $tab === 'settings'} active in{/if}">
            <form method="post" action="cache.php">
                {$jtl_token}
                <input type="hidden" name="a" value="settings" />
                <input name="tab" type="hidden" value="settings" />

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">{__('general')}</div>
                    </div>
                    <div class="card-body">
                        {foreach $settings as $setting}
                            {if $setting->cConf === 'Y'}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="{$setting->cWertName}">{$setting->cName}</label>
                                    </span>
                                    <span class="input-group-wrap">
                                        {if $setting->cInputTyp === 'selectbox'}
                                            <select name="{$setting->cWertName}" id="{$setting->cWertName}" class="form-control">
                                                {foreach $setting->ConfWerte as $wert}
                                                    <option value="{$wert->cWert}" {if isset($setting->gesetzterWert) && $setting->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                                {/foreach}
                                            </select>
                                        {elseif $setting->cInputTyp === 'number'}
                                            <input class="form-control" type="number" name="{$setting->cWertName}" id="{$setting->cWertName}" value="{if isset($setting->gesetzterWert)}{$setting->gesetzterWert}{/if}" tabindex="1" />
                                        {else}
                                            <input class="form-control" type="text" name="{$setting->cWertName}" id="{$setting->cWertName}" value="{if isset($setting->gesetzterWert)}{$setting->gesetzterWert}{/if}" tabindex="1" />
                                        {/if}
                                    </span>
                                    <span class="input-group-addon">
                                        {if $setting->cBeschreibung}
                                            {getHelpDesc cDesc=$setting->cBeschreibung}
                                        {/if}
                                    </span>
                                </div>
                            {/if}
                        {/foreach}
                    </div>
                </div>
                <a id="btn_toggle_cache" class="btn btn-default down" style="margin: 10px 0;">{__('showAdvanced')}</a>

                <div id="row_toggle_cache" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">{__('extended')}</div>
                        </div>
                        <div class="card-body">
                            {foreach $advanced_settings as $setting}
                                {if $setting->cConf === 'Y'}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <label for="{$setting->cWertName}">{$setting->cName}</label>
                                        </span>
                                        <span class="input-group-wrap">
                                            {if $setting->cInputTyp === 'selectbox'}
                                                <select name="{$setting->cWertName}" id="{$setting->cWertName}" class="form-control">
                                                    {foreach $setting->ConfWerte as $wert}
                                                        <option value="{$wert->cWert}" {if isset($setting->gesetzterWert) && $setting->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                                    {/foreach}
                                                </select>
                                            {elseif $setting->cInputTyp === 'number'}
                                                <input class="form-control" type="number" name="{$setting->cWertName}" id="{$setting->cWertName}" value="{if isset($setting->gesetzterWert)}{$setting->gesetzterWert}{/if}" tabindex="1" />
                                            {elseif $setting->cInputTyp === 'pass'}
                                                <input class="form-control" type="password" name="{$setting->cWertName}" id="{$setting->cWertName}" value="{if isset($setting->gesetzterWert)}{$setting->gesetzterWert}{/if}" tabindex="1" />
                                            {else}
                                                <input class="form-control" type="text" name="{$setting->cWertName}" id="{$setting->cWertName}" value="{if isset($setting->gesetzterWert)}{$setting->gesetzterWert}{/if}" tabindex="1" />
                                            {/if}
                                        </span>
                                        {if $setting->cBeschreibung}
                                            <span class="input-group-addon">{getHelpDesc cDesc=$setting->cBeschreibung}</span>
                                        {/if}
                                    </div>
                                {/if}
                            {/foreach}
                        </div>
                    </div>
                </div>
                <p class="submit">
                    <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                </p>
            </form>
        </div>
    </div>
</div>
