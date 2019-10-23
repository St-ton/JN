<script type="text/javascript">
function ackCheck(kPlugin, hash)
{
    var bCheck = confirm('{__('surePluginUpdate')}');
    var href = '';

    if (bCheck) {
        href += 'pluginverwaltung.php?pluginverwaltung_uebersicht=1&updaten=1&token={$smarty.session.jtl_token}&kPlugin=' + kPlugin;
        if (hash && hash.length > 0) {
            href += '#' + hash;
        }
        window.location.href = href;
    }
}

{if isset($bReload) && $bReload}
    window.location.href = window.location.href + "?h={$hinweis64}";
{/if}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=__('pluginverwaltung') cBeschreibung=__('pluginverwaltungDesc') cDokuURL=__('pluginverwaltungURL')}

<div>
    <div class="card">
        <div class="card-header">
            <div class="heading-body">
                <div class="subheading1">{__('pluginverwaltung')}</div>
            </div>
            <div class="heading-right">
                {if $hasAuth}
                    <a href="store.php" class="btn btn-outline-primary"><i class="fa fa-link"></i> {__('storeRevoke')}</a>
                {/if}
            </div>
            <hr class="mb-n3">
        </div>
        <div class="card-body">
            <div class="row">
                {if $hasAuth}
                    <div class="col-md-4 border-right">
                        <div class="text-center">
                            <h2>2</h2>
                            <p>{__('storeUpdatesAvailable')}</p>
                            <a class="btn btn-outline-primary" href="#">{__('storeListUpdates')}</a>
                        </div>
                    </div>
                    <div class="col-md-4 border-right">
                        <div class="text-center">
                            <h2>3</h2>
                            <p>{__('storePlugins')}</p>
                            <a class="btn btn-outline-primary" href="#">{__('storeListAll')}</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h2>{$smarty.now|date_format}</h2>
                            <p>{__('storeLastUpdate')}</p>
                            <a class="btn btn-outline-primary" href="#">{__('storeUpdateNow')}</a>
                        </div>
                    </div>
                {else}
                    <div class="col-md-12">
                        <div class="alert alert-default" role="alert">{__('storeNotLinkedDesc')}</div>
                        <a href="store.php" class="btn btn-primary">{__('storeLink')}</a>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>

<div id="content">
    <div id="settings">
        {if $pluginsByState|@count > 0}
            <div class="tabs">
                <nav class="tabs-nav">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {if !isset($cTab) || $cTab === 'aktiviert'} active{/if}" data-toggle="tab" role="tab" href="#aktiviert">
                                {__('activated')}<span class="badge">{$pluginsByState.status_2|@count}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {if isset($cTab) && $cTab === 'deaktiviert'} active{/if}" data-toggle="tab" role="tab" href="#deaktiviert">
                                {__('deactivated')} <span class="badge">{$pluginsByState.status_1|@count}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {if isset($cTab) && $cTab === 'probleme'} active{/if}" data-toggle="tab" role="tab" href="#probleme">
                                {__('problems')} <span class="badge">{$pluginErrorCount}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {if isset($cTab) && $cTab === 'verfuegbar'} active{/if}" data-toggle="tab" role="tab" href="#verfuegbar">
                                {__('available')} <span class="badge">{$pluginsAvailable->count()}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {if isset($cTab) && $cTab === 'fehlerhaft'} active{/if}" data-toggle="tab" role="tab" href="#fehlerhaft">
                                {__('faulty')} <span class="badge">{$pluginsErroneous->count()}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {if isset($cTab) && $cTab === 'upload'} active{/if}" data-toggle="tab" role="tab" href="#upload">{__('upload')}</a>
                        </li>
                    </ul>
                </nav>
                <div class="tab-content">
                    {include file='tpl_inc/pluginverwaltung_uebersicht_aktiviert.tpl'}
                    {include file='tpl_inc/pluginverwaltung_uebersicht_deaktiviert.tpl'}
                    {include file='tpl_inc/pluginverwaltung_uebersicht_probleme.tpl'}
                    {include file='tpl_inc/pluginverwaltung_uebersicht_verfuegbar.tpl'}
                    {include file='tpl_inc/pluginverwaltung_uebersicht_fehlerhaft.tpl'}
                    <div class="tab-pane fade" id="upload">
                        <form enctype="multipart/form-data">
                            {$jtl_token}
                            <div class="form-group">
                                <input id="plugin-install-upload" type="file" multiple class="file">
                            </div>
                            <hr>
                        </form>
                        <script>
                            var x = $('#plugin-install-upload').fileinput({ldelim}
                                uploadUrl: '{$shopURL}/{$PFAD_ADMIN}pluginverwaltung.php',
                                allowedFileExtensions : ['zip'],
                                overwriteInitial: false,
                                showPreview: false,
                                cancelClass: 'btn btn-outline-primary',
                                language: '{$language|mb_substr:0:2}',
                                maxFileSize: 100000,
                                maxFilesNum: 1
                            {rdelim}).on('fileuploaded', function(event, data, previewId, index) {ldelim}
                                var response = data.response;
                                if (response.status === 'OK') {ldelim}
                                    var wasActiveVerfuegbar = $('#verfuegbar').hasClass('active'),
                                        wasActiveFehlerhaft = $('#fehlerhaft').hasClass('active');
                                    $('#verfuegbar').replaceWith(response.html.available);
                                    $('#fehlerhaft').replaceWith(response.html.erroneous);
                                    $('a[href="#fehlerhaft"]').find('.badge').html(response.html.erroneous_count);
                                    $('a[href="#verfuegbar"]').find('.badge').html(response.html.available_count);
                                    $('#plugin-upload-success').show().removeClass('hidden');
                                    if (wasActiveFehlerhaft) {ldelim}
                                        $('#fehlerhaft').addClass('active show');
                                    {rdelim} else if (wasActiveVerfuegbar) {ldelim}
                                        $('#verfuegbar').addClass('active show');
                                    {rdelim}
                                {rdelim} else {ldelim}
                                    $('#plugin-upload-error').show().removeClass('hidden');
                                {rdelim}
                                    var fi = $('#plugin-install-upload');
                                    fi.fileinput('reset');
                                    fi.fileinput('clear');
                                    fi.fileinput('refresh');
                                    fi.fileinput('enable');
                            {rdelim});
                        </script>
                        <div id="plugin-upload-success" class="alert alert-info hidden">{__('successPluginUpload')}</div>
                        <div id="plugin-upload-error" class="alert alert-danger hidden">{__('errorPluginUpload')}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
