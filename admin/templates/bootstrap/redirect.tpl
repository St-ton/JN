{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="redirect"}
{include file='tpl_inc/seite_header.tpl' cTitel=#redirect# cBeschreibung=#redirectDesc# cDokuURL=#redirectURL#}
{include file='tpl_inc/sortcontrols.tpl'}

{assign var='cTab' value=$cTab|default:'redirects'}

<script>
    $(function () {
        {foreach $oRedirect_arr as $oRedirect}
            var $stateChecking    = $('#input-group-{$oRedirect->kRedirect} .state-checking');
            var $stateAvailable   = $('#input-group-{$oRedirect->kRedirect} .state-available');
            var $stateUnavailable = $('#input-group-{$oRedirect->kRedirect} .state-unavailable');

            {if $oRedirect->cAvailable === 'y'}
                $stateChecking.hide();
                $stateAvailable.show();
            {elseif $oRedirect->cAvailable === 'n'}
                $stateChecking.hide();
                $stateUnavailable.show();
            {else}
                checkUrl({$oRedirect->kRedirect}, true);
            {/if}
        {/foreach}
    });

    function checkUrl(kRedirect, doUpdate)
    {
        doUpdate = doUpdate || false;

        var $stateChecking    = $('#input-group-' + kRedirect + ' .state-checking');
        var $stateAvailable   = $('#input-group-' + kRedirect + ' .state-available');
        var $stateUnavailable = $('#input-group-' + kRedirect + ' .state-unavailable');

        $stateChecking.show();
        $stateAvailable.hide();
        $stateUnavailable.hide();

        function checkUrlCallback(result)
        {
            $stateChecking.hide();
            $stateAvailable.hide();
            $stateUnavailable.hide();

            if (result === true) {
                $stateAvailable.show();
            } else {
                $stateUnavailable.show();
            }
        }

        if(doUpdate) {
            ioCall('updateRedirectState', [kRedirect], checkUrlCallback);
        } else {
            ioCall('redirectCheckAvailability', [$('#cToUrl-' + kRedirect).val()], checkUrlCallback);
        }
    }

    function redirectTypeahedDisplay(item)
    {
        return '/' + item.cSeo;
    }

    function redirectTypeahedSuggestion(item)
    {
        var type = '';
        switch(item.cKey) {
            case 'kLink': type = 'Seite'; break;
            case 'kNews': type = 'News'; break;
            case 'kNewsKategorie': type = 'News-Kategorie'; break;
            case 'kNewsMonatsUebersicht': type = 'News-Montas&uuml;bersicht'; break;
            case 'kUmfrage': type = 'Umfrage'; break;
            case 'kArtikel': type = 'Artikel'; break;
            case 'kKategorie': type = 'Kategorie'; break;
            case 'kHersteller': type = 'Hersteller'; break;
            case 'kMerkmalWert': type = 'Merkmal-Wert'; break;
            case 'suchspecial': type = 'Suchspecial'; break;
            default: type = 'Anderes'; break;
        }
        return '<span>/' + item.cSeo +
            ' <small class="text-muted">- ' + type + '</small></span>';
    }
</script>

<ul class="nav nav-tabs" role="tablist">
    <li role="presentation"{if $cTab === 'redirects'} class="active"{/if}>
        <a data-toggle="tab" role="tab" href="#redirects">Redirects</a>
    </li>
    <li role="presentation"{if $cTab === 'new_redirect'} class="active"{/if}>
        <a data-toggle="tab" role="tab" href="#new_redirect">Neuer Redirect</a>
    </li>
</ul>

<div class="tab-content">
    <div role="tabpanel" class="tab-pane fade{if $cTab === 'redirects'} active in{/if}" id="redirects">
        <div class="panel panel-default">
            {include file='tpl_inc/filtertools.tpl' oFilter=$oFilter}
            {include file='tpl_inc/pagination.tpl' oPagination=$oPagination cAnchor='redirects'}
            <form method="post">
                {$jtl_token}
                {if $oRedirect_arr|@count > 0}
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width:24px"></th>
                                <th style="width:35%">URL {call sortControls oPagination=$oPagination nSortBy=0}</th>
                                <th>Ziel-URL {call sortControls oPagination=$oPagination nSortBy=1}</th>
                                <th style="width:85px">Aufrufe {call sortControls oPagination=$oPagination nSortBy=2}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $oRedirect_arr as $oRedirect}
                                <tr>
                                    <td>
                                        <input type="checkbox" name="redirects[{$oRedirect->kRedirect}][enabled]" value="1"
                                               id="check-{$oRedirect->kRedirect}">
                                    </td>
                                    <td>
                                        <label for="check-{$oRedirect->kRedirect}">
                                            <a href="{$oRedirect->cFromUrl}" target="_blank"
                                               {if $oRedirect->cFromUrl|strlen > 50}data-toggle="tooltip"
                                               data-placement="bottom" title="{$oRedirect->cFromUrl}"{/if}>
                                                {$oRedirect->cFromUrl|truncate:50}
                                            </a>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="input-group" id="input-group-{$oRedirect->kRedirect}">
                                            <span class="input-group-addon alert-info state-checking">
                                                <i class="fa fa-spinner fa-pulse"></i>
                                            </span>
                                            <span class="input-group-addon alert-success state-available" style="display:none;">
                                                <i class="fa fa-check"></i>
                                            </span>
                                            <span class="input-group-addon alert-danger state-unavailable" style="display:none;">
                                                <i class="fa fa-warning"></i>
                                            </span>
                                            <input class="form-control" name="redirects[{$oRedirect->kRedirect}][cToUrl]"
                                                   value="{$oRedirect->cToUrl}" id="cToUrl-{$oRedirect->kRedirect}"
                                                   onblur="checkUrl({$oRedirect->kRedirect})">
                                            <script>
                                                enableTypeahead(
                                                    '#cToUrl-{$oRedirect->kRedirect}', 'getSeos',
                                                    redirectTypeahedDisplay, redirectTypeahedSuggestion,
                                                    checkUrl.bind(null, {$oRedirect->kRedirect}, false)
                                                );
                                            </script>
                                        </div>
                                    </td>
                                    <td>
                                        {if $oRedirect->nCount > 0}
                                            <span class="badge">{$oRedirect->nCount}</span>
                                        {/if}
                                    </td>
                                    <td></td>
                                </tr>
                            {/foreach}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>
                                    <input type="checkbox" name="ALLMSGS" id="ALLMSGS" onclick="AllMessages(this.form);">
                                </td>
                                <td colspan="4">
                                    <label for="ALLMSGS">Alle ausw&auml;hlen</label>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                {elseif $nTotalRedirectCount > 0}
                    <div class="alert alert-info" role="alert">{#noFilterResults#}</div>
                {else}
                    <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                {/if}
                <div class="panel-footer">
                    <div class="btn-group">
                        {if $oRedirect_arr|@count > 0}
                            <button name="action" value="save" class="btn btn-primary">
                                <i class="fa fa-save"></i> {#save#}
                            </button>
                            <button name="action" value="delete" class="btn btn-danger">
                                <i class="fa fa-trash"></i> {#deleteSelected#}
                            </button>
                            <button name="action" value="delete_all" class="btn btn-warning">
                                Alle ohne Weiterleitung l&ouml;schen
                            </button>
                            {include file='tpl_inc/csv_export_btn.tpl' exporterId='redirects'}
                        {/if}
                        {include file='tpl_inc/csv_import_btn.tpl' importerId='redirects'}
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div role="tabpanel" class="tab-pane fade{if $cTab === 'new_redirect'} active in{/if}" id="new_redirect">
        <form method="post">
            {$jtl_token}
            <div class="panel panel-default settings">
                <div class="panel-heading">
                    <h3 class="panel-title">Neue Weiterleitung</h3>
                </div>
                <div class="panel-body">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cFromUrl">Quell-URL:</label>
                        </span>
                        <input class="form-control" id="cFromUrl" name="cFromUrl" required
                               {if !empty($cFromUrl)}value="{$cFromUrl}"{/if}>
                    </div>
                    <div class="input-group" id="input-group-0">
                        <span class="input-group-addon">
                            <label for="cToUrl">Ziel-URL:</label>
                        </span>
                        <span class="input-group-addon alert-info state-checking" style="display:none;">
                            <i class="fa fa-spinner fa-pulse"></i>
                        </span>
                        <span class="input-group-addon alert-success state-available" style="display:none;">
                            <i class="fa fa-check"></i>
                        </span>
                        <span class="input-group-addon alert-danger state-unavailable">
                            <i class="fa fa-warning"></i>
                        </span>
                        <input class="form-control" id="cToUrl-0" name="cToUrl" required
                               onblur="checkUrl(0)" {if !empty($cToUrl)}value="{$cToUrl}"{/if}>
                        <script>
                            enableTypeahead(
                                '#cToUrl-0', 'getSeos', redirectTypeahedDisplay, redirectTypeahedSuggestion,
                                checkUrl.bind(null, 0, false)
                            )
                        </script>
                    </div>
                </div>
                <div class="panel-footer">
                    <button name="action" value="new" class="btn btn-primary">
                        <i class="fa fa-save"></i> Anlegen
                    </button>
                </div>
            </div>
        </form>
        <form method="post" enctype="multipart/form-data">
            {$jtl_token}
            <div class="panel panel-default settings">
                <div class="panel-heading">
                    <h3 class="panel-title">CSV-Import</h3>
                </div>
                <div class="panel-body">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cFile">Datei:</label>
                        </span>
                        <input class="form-control" name="cFile" type="file" required>
                    </div>
                </div>
                <div class="panel-footer">
                    <button name="action" value="csvimport" class="btn btn-primary">
                        Importieren
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}