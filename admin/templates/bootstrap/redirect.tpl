{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='redirect'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('redirect') cBeschreibung=__('redirectDesc') cDokuURL=__('redirectURL')}
{include file='tpl_inc/sortcontrols.tpl'}

{assign var=cTab value=$cTab|default:'redirects'}

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
            case 'kNewsMonatsUebersicht': type = 'News-Montasübersicht'; break;
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

    function toggleReferer(kRedirect)
    {
        var $refTr  = $('#referer-tr-' + kRedirect);
        var $refDiv = $('#referer-div-' + kRedirect);

        if(!$refTr.is(':visible')) {
            $refTr.show();
            $refDiv.slideDown();
        } else {
            $refDiv.slideUp(500, $refTr.hide.bind($refTr));
        }
    }
</script>

<ul class="nav nav-tabs" role="tablist">
    <li role="presentation"{if $cTab === 'redirects'} class="active"{/if}>
        <a data-toggle="tab" role="tab" href="#redirects">{__('overview')}</a>
    </li>
    <li role="presentation"{if $cTab === 'new_redirect'} class="active"{/if}>
        <a data-toggle="tab" role="tab" href="#new_redirect">{__('create')}</a>
    </li>
</ul>

<div class="tab-content">
    <div role="tabpanel" class="tab-pane fade{if $cTab === 'redirects'} active show{/if}" id="redirects">
        {include file='tpl_inc/filtertools.tpl' oFilter=$oFilter}
        {include file='tpl_inc/pagination.tpl' pagination=$pagination cAnchor='redirects'}
        <div class="card">
            <div class="card-body">
                <form method="post">
                    {$jtl_token}
                    {if $oRedirect_arr|@count > 0}
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width:24px"></th>
                                    <th style="width:35%">{__('redirectFrom')} {call sortControls pagination=$pagination nSortBy=0}</th>
                                    <th>{__('redirectTo')} {call sortControls pagination=$pagination nSortBy=1}</th>
                                    <th style="width:85px">{__('redirectRefererCount')} {call sortControls pagination=$pagination nSortBy=2}</th>
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
                                        <td>
                                            {if $oRedirect->nCount > 0}
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-default btn-sm" title="{__('details')}"
                                                            onclick="toggleReferer({$oRedirect->kRedirect});">
                                                        <i class="fa fa-list"></i>
                                                    </button>
                                                </div>
                                            {/if}
                                        </td>
                                    </tr>
                                    {if $oRedirect->nCount > 0}
                                        <tr id="referer-tr-{$oRedirect->kRedirect}" style="display:none;">
                                            <td></td>
                                            <td colspan="5">
                                                <div id="referer-div-{$oRedirect->kRedirect}" style="display:none;">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>{__('redirectReferer')}</th>
                                                                <th>{__('date')}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {foreach $oRedirect->oRedirectReferer_arr as $oRedirectReferer}
                                                                <tr>
                                                                    <td>
                                                                        {if $oRedirectReferer->kBesucherBot > 0}
                                                                            {if $oRedirectReferer->cBesucherBotName|strlen > 0}
                                                                                {$oRedirectReferer->cBesucherBotName}
                                                                            {else}
                                                                                {$oRedirectReferer->cBesucherBotAgent}
                                                                            {/if}
                                                                            (Bot)
                                                                        {elseif $oRedirectReferer->cRefererUrl|strlen > 0}
                                                                            <a href="{$oRedirectReferer->cRefererUrl}" target="_blank">
                                                                                {$oRedirectReferer->cRefererUrl}
                                                                            </a>
                                                                        {else}
                                                                            <i>{__('redirectRefererDirect')}</i>
                                                                        {/if}
                                                                    </td>
                                                                    <td>
                                                                        {$oRedirectReferer->dDate|date_format:'%d.%m.%Y - %H:%M:%S'}
                                                                    </td>
                                                                </tr>
                                                            {/foreach}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    {/if}
                                {/foreach}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="ALLMSGS" id="ALLMSGS" onclick="AllMessages(this.form);">
                                    </td>
                                    <td colspan="4">
                                        <label for="ALLMSGS">{__('globalSelectAll')}</label>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    {elseif $nTotalRedirectCount > 0}
                        <div class="alert alert-info" role="alert">{__('noFilterResults')}</div>
                    {else}
                        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                    {/if}
                    <div class="card-footer">
                        <div class="btn-group">
                            {if $oRedirect_arr|@count > 0}
                                <button name="action" value="save" class="btn btn-primary">
                                    <i class="fa fa-save"></i> {__('save')}
                                </button>
                                <button name="action" value="delete" class="btn btn-danger">
                                    <i class="fa fa-trash"></i> {__('deleteSelected')}
                                </button>
                                <button name="action" value="delete_all" class="btn btn-warning">
                                    {__('redirectDelUnassigned')}
                                </button>
                                {include file='tpl_inc/csv_export_btn.tpl' exporterId='redirects'}
                            {/if}
                            {include file='tpl_inc/csv_import_btn.tpl' importerId='redirects'}
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div role="tabpanel" class="tab-pane fade{if $cTab === 'new_redirect'} active show{/if}" id="new_redirect">
        <form method="post">
            {$jtl_token}
            <div class="card settings">
                <div class="card-header">
                    <div class="subheading1">{__('redirectNew')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cFromUrl">{__('redirectFrom')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" id="cFromUrl" name="cFromUrl" required
                                   {if !empty($cFromUrl)}value="{$cFromUrl}"{/if}>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center" id="input-group-0">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cToUrl-0">{__('redirectTo')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" id="cToUrl-0" name="cToUrl" required
                                   onblur="checkUrl(0)" {if !empty($cToUrl)}value="{$cToUrl}"{/if}>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3" style="display:none;">
                            <i class="fa fa-spinner fa-pulse text-info state-checking"></i>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3" style="display:none;">
                            <i class="fa fa-check text-success state-available"></i>
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                            <i class="fa fa-warning text-danger state-unavailable"></i>
                        </div>
                        <script>
                            enableTypeahead(
                                '#cToUrl-0', 'getSeos', redirectTypeahedDisplay, redirectTypeahedSuggestion,
                                checkUrl.bind(null, 0, false)
                            )
                        </script>
                    </div>
                </div>
                <div class="card-footer">
                    <button name="action" value="new" class="btn btn-primary">
                        <i class="fa fa-save"></i> {__('create')}
                    </button>
                </div>
            </div>
        </form>
        <form method="post" enctype="multipart/form-data">
            {$jtl_token}
            <div class="card settings">
                <div class="card-header">
                    <div class="subheading1">{__('redirectCsvImport')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cFile">{__('file')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" name="cFile" type="file" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button name="action" value="csvimport" class="btn btn-primary">
                        {__('import')}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
