{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="redirect"}
{include file='tpl_inc/seite_header.tpl' cTitel=#redirect# cBeschreibung=#redirectDesc# cDokuURL=#redirectURL#}
{include file='tpl_inc/sortcontrols.tpl'}

<script>{literal}
    $(document).ready(function () {
        init_simple_search(function (type, res) {
            $('input.simple_search').val(res.cUrl)
        });
        $('.showEditor').click(function () {
            $('input.cToUrl').removeClass('simple_search');
            $(this).parent().find('input.cToUrl').addClass('simple_search');
            show_simple_search($(this).attr('id'));
            return false;
        });
        $('.import').click(function () {
            var $csvimport = $('.csvimport');
            if ($csvimport.css('display') === 'none') {
                $csvimport.fadeIn();
            } else {
                $csvimport.fadeOut();
            }
        });
        {/literal}
            {foreach $oRedirect_arr as $oRedirect}
                check_url({$oRedirect->kRedirect}, '{$oRedirect->cToUrl}');
            {/foreach}
            check_url('cToUrl', '{if isset($cPost_arr.cToUrl)}{$cPost_arr.cToUrl}{/if}');
        {literal}
    });
    
    redirect_search = function (id,search) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'redirect.php',
            data: {
                {/literal}
                    'jtl_token': '{$smarty.session.jtl_token}',
                {literal}
                'aData[action]': 'search',
                'aData[search]': ( (search.substr(0, 1) != '/') ? search.substr(0) : search.substr(1) )
            },
            success: function (data, textStatus, jqXHR) {
                if (search.length > 1) {
                    var ret = '',
                        i;
                    $('#resSearch_' + id + ' li').remove();
                    if (data.article.length > 0) {
                        ret += '<li class="dropdown-header">Artikel</li>';
                        for (i = 0; i < data.article.length; i++) {
                            ret += '<li onclick="$(\'#url_' + id + '\').val(\'/' + data.article[i].cUrl + '\');check_url(\'' + id + '\',$(\'#url_' + id + '\').val());return false;">';
                            ret += '<a href="#">/' + data.article[i].cUrl + '</a></li>';
                        }
                    }
                    if (data.category.length > 0) {
                        ret += '<li class="dropdown-header">Kategorie</li>';
                        for (i = 0; i < data.category.length; i++) {
                            ret += '<li onclick="$(\'#url_' + id + '\').val(\'/' + data.category[i].cUrl + '\');check_url(\'' + id + '\',$(\'#url_' + id + '\').val());return false;">';
                            ret += '<a href="#">/' + data.category[i].cUrl + '</a></li>';
                        }
                    }
                    if (data.manufacturer.length > 0) {
                        ret += '<li class="dropdown-header">Hersteller</li>';
                        for (i = 0; i < data.manufacturer.length; i++) {
                            ret += '<li onclick="$(\'#url_' + id + '\').val(\'/' + data.manufacturer[i].cUrl + '\');check_url(\'' + id + '\',$(\'#url_' + id + '\').val());return false;">';
                            ret += '<a href="#">/' + data.manufacturer[i].cUrl + '</a></li>';
                        }
                    }
                    $('#resSearch_' + id).append(ret);
                    if (ret) {
                        $('#frm_' + id + ' .input-group-btn').addClass('open');
                    } else {
                        $('#frm_' + id + ' .input-group-btn').removeClass('open');
                    }
                }
            }
        });
    };
    
    check_url = function(id,url) {
        var $stateChecking = $('#frm_' + id + ' .state-checking');
        var $stateAvailable = $('#frm_' + id + ' .state-available');
        var $stateUnavailable = $('#frm_' + id + ' .state-unavailable');
        $stateChecking.show();
        $stateAvailable.hide();
        $stateUnavailable.hide();
        $.ajax({
            type: 'POST',
            url: 'redirect.php',
            data: {
                {/literal}
                    'jtl_token': '{$smarty.session.jtl_token}',
                {literal}
                'aData[action]': 'check_url',
                'aData[url]': url
            },
            success: function (data, textStatus, jqXHR) {
                $stateChecking.hide();
                $stateAvailable.hide();
                $stateUnavailable.hide();
                if (data == '1') {
                    $stateAvailable.show();
                } else {
                    $stateUnavailable.show();
                }
            }
        });
    };
{/literal}</script>

<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'redirects'} active{/if}">
            <a data-toggle="tab" role="tab" href="#redirects">Redirects</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'new_redirect'} active{/if}">
            <a data-toggle="tab" role="tab" href="#new_redirect">Neuer Redirect</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="redirects" class="tab-pane fade {if !isset($cTab) || $cTab === 'redirects'} active in{/if}">
            <div class="panel panel-default">
                {if $nRedirectCount > 0}
                    {include file='tpl_inc/filtertools.tpl' oFilter=$oFilter}
                {/if}
                {if $oRedirect_arr|@count > 0}
                    {include file='tpl_inc/pagination.tpl' oPagination=$oPagination cAnchor='redirects'}
                {/if}
                <form id="frmRedirect" action="redirect.php" method="post">
                    {$jtl_token}
                    <input type="hidden" name="aData[action]" value="save">
                    {if $oRedirect_arr|@count > 0}
                        <table class="list table">
                            <thead>
                            <tr>
                                <th class="tcenter" style="width:24px"></th>
                                <th class="tleft" style="width:35%;">
                                    URL {call sortControls oPagination=$oPagination nSortBy=0}
                                </th>
                                <th class="tleft">
                                    Wird weitergeleitet nach {call sortControls oPagination=$oPagination nSortBy=1}
                                </th>
                                <th class="tright" style="width:85px">
                                    Aufrufe {call sortControls oPagination=$oPagination nSortBy=2}
                                </th>
                                <th class="tcenter">Optionen</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach from=$oRedirect_arr item="oRedirect"}
                                <tr>
                                    {assign var=redirectCount value=$oRedirect->nCount}
                                    <td class="tcenter" style="vertical-align:middle;">
                                        <input type="checkbox"  name="aData[redirect][{$oRedirect->kRedirect}][active]" value="1" />
                                    </td>
                                    <td class="tleft" style="vertical-align:middle;">
                                        <a href="{$oRedirect->cFromUrl}" target="_blank">{$oRedirect->cFromUrl|truncate:52:"..."}</a>
                                    </td>
                                    <td class="tleft">
                                        <div id="frm_{$oRedirect->kRedirect}" class="input-group input-group-sm">
                                            <span class="input-group-addon alert-info state-checking"><i class="fa fa-spinner"></i></span>
                                            <span class="input-group-addon alert-success state-available" style="display:none;"><i class="fa fa-check"></i></span>
                                            <span class="input-group-addon alert-danger state-unavailable" style="display:none;"><i class="fa fa-warning"></i></span>
                                            <input id="url_{$oRedirect->kRedirect}"
                                                   name="aData[redirect][{$oRedirect->kRedirect}][url]" type="text"
                                                   class="form-control cToUrl" autocomplete="off"
                                                   value="{$oRedirect->cToUrl}"
                                                   onblur="check_url('{$oRedirect->kRedirect}', this.value);">
                                            <script>
                                                enableTypeahead(
                                                    '#url_{$oRedirect->kRedirect}', 'getSeos', 'cUrl',
                                                    'cUrl',
                                                    function (item) {
                                                        check_url('{$oRedirect->kRedirect}', item.url);
                                                    }
                                                );
                                            </script>
                                        </div>
                                    </td>
                                    <td class="text-right" style="vertical-align:middle;"><span class="badge">{$redirectCount}</span></td>
                                    <td class="tcenter">
                                        {if $redirectCount > 0}
                                            <a class="btn btn-sm btn-default" data-toggle="collapse" href="#collapse-{$oRedirect->kRedirect}">Details</a>
                                        {/if}
                                    </td>
                                </tr>
                                {if $redirectCount > 0}
                                    <tr class="collapse" id="collapse-{$oRedirect->kRedirect}">
                                        <td></td>
                                        <td colspan="5">
                                            <table class="innertable table">
                                                <thead>
                                                <tr>
                                                    <th class="tleft">Verweis</th>
                                                    <th class="tcenter" width="200">Datum</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                {foreach from=$oRedirect->oRedirectReferer_arr item="oRedirectReferer"}
                                                    <tr>
                                                        <td class="tleft">
                                                            {if $oRedirectReferer->kBesucherBot > 0}
                                                                {if $oRedirectReferer->cBesucherBotName|strlen > 0}
                                                                    {$oRedirectReferer->cBesucherBotName}
                                                                {else}
                                                                    {$oRedirectReferer->cBesucherBotAgent}
                                                                {/if}
                                                                (Bot)
                                                            {elseif $oRedirectReferer->cRefererUrl|strlen > 0}
                                                                <a href="{$oRedirectReferer->cRefererUrl}" target="_blank">{$oRedirectReferer->cRefererUrl}</a>
                                                            {else}
                                                                <i>Direkteinstieg</i>
                                                            {/if}
                                                        </td>
                                                        <td class="tcenter">
                                                            {$oRedirectReferer->dDate|date_format:"%d.%m.%Y %H:%M:%S"}
                                                        </td>
                                                    </tr>
                                                {/foreach}
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                {/if}
                            {/foreach}
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="5">
                                    <label for="ALLMSGS"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" />&nbsp; Alle ausw&auml;hlen</label>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    {elseif $nRedirectCount > 0}
                        <div class="alert alert-info" role="alert">{#noFilterResults#}</div>
                    {else}
                        <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                    {/if}
                    <div class="panel-footer">
                        <div class="btn-group">
                            {if $oRedirect_arr|@count > 0}
                                <button type="button"
                                        onclick="$('[name=\'aData\[action\]\']').val('save');$('#frmRedirect').submit();"
                                        value="{#save#}" class="btn btn-primary" title="{#save#}">
                                    <i class="fa fa-save"></i> {#save#}
                                </button>
                                <button type="button"
                                        onclick="$('[name=\'aData\[action\]\']').val('delete');$('#frmRedirect').submit();"
                                        name="delete" value="Auswahl l&ouml;schen" title="Auswahl l&ouml;schen"
                                        class="btn btn-danger">
                                    <i class="fa fa-trash"></i> {#deleteSelected#}
                                </button>
                                <button type="button"
                                        onclick="$('[name=\'aData\[action\]\']').val('delete_all');$('#frmRedirect').submit();"
                                        name="delete_all" value="Alle ohne Weiterleitung l&ouml;schen"
                                        title="Alle ohne Weiterleitung l&ouml;schen" class="btn btn-warning">
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
        <div id="new_redirect" class="tab-pane fade {if isset($cTab) && $cTab === 'new_redirect'} active in{/if}">
            <button class="btn btn-primary import" style="margin-bottom: 15px;">CSV-Import durchf&uuml;hren</button>
            <div class="csvimport" style="display: none;">
                <form method="post" enctype="multipart/form-data">
                    {$jtl_token}
                    <input name="aData[action]" type="hidden" value="csvimport" />
                    <table class="table">
                        <tbody>
                        <tr>
                            <td>Datei:</td>
                            <td><input class="form-control" name="cFile" type="file" /></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input name="submit" type="submit" class="btn blue btn-default" value="Importieren" /></td>
                        </tr>
                        </tbody>
                    </table>
                </form>
            </div>
            <form method="post" action="#new_redirect">
                {$jtl_token}
                <div class="panel panel-default settings">
                    <div class="panel-heading">
                        <h3 class="panel-title">Neue Weiterleitung</h3>
                    </div>
                    <div class="panel-body">
                        <input name="aData[action]" type="hidden" value="new" />
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cSource">Quell-URL:</label>
                            </span>
                            <input class="form-control" id="cSource" name="cSource" type="text" placeholder="Quell Url" value="{if isset($cPost_arr.cSource)}{$cPost_arr.cSource}{/if}" />
                        </div>
                        <div id="frm_cToUrl" class="input-group">
                            <span class="input-group-addon">
                                <label for="cToUrl">Ziel-URL:</label>
                            </span>
                            <span class="input-group-addon alert-info state-checking"><i class="fa fa-spinner"></i></span>
                            <span class="input-group-addon alert-success state-available" style="display:none;"><i class="fa fa-check"></i></span>
                            <span class="input-group-addon alert-danger state-unavailable" style="display:none;"><i class="fa fa-warning"></i></span>
                            <input id="url_cToUrl" name="cToUrl" type="text" class="form-control cToUrl"
                                   autocomplete="off" value="{if isset($cPost_arr.cToUrl)}{$cPost_arr.cToUrl}{/if}"
                                   onblur="check_url('cToUrl', this.value);" placeholder="Ziel-URL">
                            <script>
                                enableTypeahead('#url_cToUrl', 'getSeos', 'cUrl', 'cUrl', function (item) {
                                    check_url('cToUrl', item.url);
                                });
                            </script>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button name="submit" type="submit" value="Speichern" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>

{include file='tpl_inc/footer.tpl'}