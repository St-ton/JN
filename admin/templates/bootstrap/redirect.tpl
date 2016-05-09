{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="redirect"}
{include file='tpl_inc/seite_header.tpl' cTitel=#redirect# cBeschreibung=#redirectDesc# cDokuURL=#redirectURL#}

<script>
    {literal}
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
            if ($('.csvimport').css('display') === 'none') {
                $('.csvimport').fadeIn();
            } else {
                $('.csvimport').fadeOut();
            }
        });
    });
    {/literal}
    
    redirect_search = function (id,search) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'redirect.php',
            data: {
                'aData[action]': 'search',
                'aData[search]': ( (search.substr(0, 1) != '/') ? search.substr(0) : search.substr(1) )
            },
            success: function (data, textStatus, jqXHR) {
                if (search.length > 1) {
                    var ret = '',
                        i;
                    $('#resSearch' + id + ' li').remove();
                    if (data.article.length > 0) {
                        ret += '<li class="dropdown-header">Artikel</li>';
                        for (i = 0; i < data.article.length; i++) {
                            ret += '<li onclick="$(\'#url' + id + '\').val(\'/' + data.article[i].cUrl + '\');check_url(\'' + id + '\',$(\'#url' + id + '\').val());return false;"><a href="#">/' + data.article[i].cUrl + '</a></li>';
                        }
                    }
                    if (data.category.length > 0) {
                        ret += '<li class="dropdown-header">Kategorie</li>';
                        for (i = 0; i < data.category.length; i++) {
                            ret += '<li onclick="$(\'#url' + id + '\').val(\'/' + data.category[i].cUrl + '\');check_url(\'' + id + '\',$(\'#url' + id + '\').val());return false;"><a href="#">/' + data.category[i].cUrl + '</a></li>';
                        }
                    }
                    if (data.manufacturer.length > 0) {
                        ret += '<li class="dropdown-header">Hersteller</li>';
                        for (i = 0; i < data.manufacturer.length; i++) {
                            ret += '<li onclick="$(\'#url' + id + '\').val(\'/' + data.manufacturer[i].cUrl + '\');check_url(\'' + id + '\',$(\'#url' + id + '\').val());return false;"><a href="#">/' + data.manufacturer[i].cUrl + '</a></li>';
                        }
                    }
                    $('#resSearch' + id).append(ret);
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
        $.ajax({
            type: 'POST',
            url: 'redirect.php',
            data: {
                'aData[action]': 'check_url',
                'aData[url]': url
            },
            success: function (data, textStatus, jqXHR) {
                $('#frm_' + id + ' .alert-success').hide();
                $('#frm_' + id + ' .alert-danger').hide();

                if (data == 1) {
                    $('#frm_' + id + ' .alert-success').show();
                } else {
                    $('#frm_' + id + ' .alert-danger').show();
                }
            }
        });
    };

</script>

<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'redirects'} active{/if}">
            <a data-toggle="tab" role="tab" href="#redirects">Redirects</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'new_redirect'} active{/if}">
            <a data-toggle="tab" role="tab" href="#new_redirect">Neuer Redirect</a>
        </li>{*
        <li class="tab{if isset($cTab) && $cTab === 'config'} active{/if}">
            <a data-toggle="tab" role="tab" href="#config">Einstellungen</a>
        </li>*}
    </ul>
    <div class="tab-content">
        <div id="redirects" class="tab-pane fade {if !isset($cTab) || $cTab === 'redirects'} active in{/if}">
            {if $oRedirect_arr|@count > 0}
                <div class="panel panel-default">
                    <form id="frmFilter" action="redirect.php" method="post">
                        {$jtl_token}
                        <input type="hidden" name="cSortierFeld" value="{$cSortierFeld}">
                        <input type="hidden" name="cSortierung" value="{$cSortierung}">

                        <div class="panel-heading">
                            <h3 class="panel-title">Redirects</h3>
                        </div>
                        <div class="panel-body">
                            <div class="pull-right p50">
                                <div class="input-group item">
                                    <span class="input-group-addon">
                                        <label for="bUmgeleiteteUrls"> Filter</label>
                                    </span>
                                    <span class="input-group-wrap">
                                        <select class="form-control" id="bUmgeleiteteUrls" name="bUmgeleiteteUrls">
                                            <option value="0"{if $bUmgeleiteteUrls == '0'} selected="selected"{/if}>alle</option>
                                            <option value="1"{if $bUmgeleiteteUrls == '1'} selected="selected"{/if}>nur umgeleitet</option>
                                            <option value="2"{if $bUmgeleiteteUrls == '2'} selected="selected"{/if}>nur ohne Umleitung
                                            </option>
                                        </select>
                                    </span>
                                    <input type="text" class="form-control" name="cSuchbegriff" value="{$cSuchbegriff}" />
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="submit"><i class="fa fa-search"></i>&nbsp;</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form id="frmRedirect" action="redirect.php?s1={$oBlaetterNavi->nAktuelleSeite}" method="post">
                        {$jtl_token}
                        <input type="hidden" name="aData[action]" value="save">
                        <table class="list table">
                            <thead>
                            <tr>
                                <th class="tcenter" style="width:24px"></th>
                                <th class="tleft" style="width:35%;">Url
                                    {if $cSortierFeld == 'cFromUrl' && $cSortierung == 'DESC'}
                                    <a href="#" onclick="$('[name=\'cSortierFeld\']').val('cFromUrl');$('[name=\'cSortierung\']').val('ASC');$('#frmFilter').submit();return false;"><i class="fa fa-sort-asc"></i></a>
                                    {else}
                                    <a href="#" onclick="$('[name=\'cSortierFeld\']').val('cFromUrl');$('[name=\'cSortierung\']').val('DESC');$('#frmFilter').submit();return false;"><i class="fa fa-sort-desc"></i></a>
                                    {/if}
                                </th>
                                <th class="tleft">Wird weitergeleitet nach
                                    {if $cSortierFeld == 'cToUrl' && $cSortierung == 'DESC'}
                                        <a href="#" onclick="$('[name=\'cSortierFeld\']').val('cToUrl');$('[name=\'cSortierung\']').val('ASC');$('#frmFilter').submit();return false;"><i class="fa fa-sort-asc"></i></a>
                                    {else}
                                        <a href="#" onclick="$('[name=\'cSortierFeld\']').val('cToUrl');$('[name=\'cSortierung\']').val('DESC');$('#frmFilter').submit();return false;"><i class="fa fa-sort-desc"></i></a>
                                    {/if}
                                </th>
                                <th class="tright" style="width:85px">Aufrufe
                                    {if $cSortierFeld == 'nCount' && $cSortierung == 'DESC'}
                                        <a href="#" onclick="$('[name=\'cSortierFeld\']').val('nCount');$('[name=\'cSortierung\']').val('ASC');$('#frmFilter').submit();return false;"><i class="fa fa-sort-asc"></i></a>
                                    {else}
                                        <a href="#" onclick="$('[name=\'cSortierFeld\']').val('nCount');$('[name=\'cSortierung\']').val('DESC');$('#frmFilter').submit();return false;"><i class="fa fa-sort-desc"></i></a>
                                    {/if}
                                </th>
                                <th class="tcenter">Optionen</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach from=$oRedirect_arr item="oRedirect"}
                                <tr>
                                    {assign var=redirectCount value=$oRedirect->oRedirectReferer_arr|@count}
                                    <td class="tcenter" style="vertical-align:middle;">
                                        <input type="checkbox"  name="aData[redirect][{$oRedirect->kRedirect}][active]" value="1" />
                                    </td>
                                    <td class="tleft" style="vertical-align:middle;">
                                        <a href="{$oRedirect->cFromUrl}" target="_blank">{$oRedirect->cFromUrl|truncate:52:"..."}</a>
                                    </td>
                                    <td class="tleft">
                                        <div id="frm_{$oRedirect->kRedirect}" class="input-group input-group-sm" style="margin-right:30px;">
                                            <span class="input-group-addon alert-success" {if $oRedirect->cToUrl == ''}style="display:none;"{/if}><i class="fa fa-check"></i></span>
                                            <span class="input-group-addon alert-danger" {if $oRedirect->cToUrl != ''}style="display:none;"{/if}><i class="fa fa-warning"></i></span>
                                            <input id="url{$oRedirect->kRedirect}" name="aData[redirect][{$oRedirect->kRedirect}][url]" type="text" class="form-control cToUrl" autocomplete="off" onblur="check_url('{$oRedirect->kRedirect}',this.value);" onkeyup="redirect_search('{$oRedirect->kRedirect}', this.value );" value="{$oRedirect->cToUrl}"  />
                                            <div class="input-group-btn" style="width:100%;display:block;top:100%;">
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></button>
                                                <ul class="dropdown-menu" style="min-width:100%;" id="resSearch{$oRedirect->kRedirect}"></ul>
                                            </div>
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
                        <div class="panel-footer">
                            <div class="btn-group">
                                <button type="button" onclick="$('[name=\'aData\[action\]\']').val('save');$('#frmRedirect').submit();" value="{#save#}" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
                                <button type="button" onclick="$('[name=\'aData\[action\]\']').val('delete');$('#frmRedirect').submit();" name="delete" value="Auswahl l&ouml;schen" class="btn btn-danger"><i class="fa fa-trash"></i> Markierte l&ouml;schen</button>
                                <button type="button" onclick="$('[name=\'aData\[action\]\']').val('delete_all');$('#frmRedirect').submit();" name="delete_all" value="Alle ohne Weiterleitung l&ouml;schen" class="btn btn-warning">Alle ohne Weiterleitung l&ouml;schen</button>
                            </div>

                            <div class="pull-right">
                            <!--  Pagination unten -->
                            {include file='pagination.tpl' cSite=1 cUrl='redirect.php' oBlaetterNavi=$oBlaetterNavi hash='#redirects'}
                            </div>
                        </div>
                    </form>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
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
            <form method="post">
                {$jtl_token}
                <div class="panel panel-default">
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
                        <div id="frm_cDestiny" class="input-group" style="margin-right:30px;">
                            <span class="input-group-addon">
                                <label for="cDestiny">Ziel-URL:</label>
                            </span>
                            <span class="input-group-addon alert-success"><i class="fa fa-check"></i></span>
                            <span class="input-group-addon alert-danger" style="display:none;"><i class="fa fa-warning"></i></span>
                            <input id="urlcDestiny" name="cDestiny" type="text" class="form-control cToUrl" autocomplete="off" onblur="check_url('cDestiny',this.value);" onkeyup="redirect_search('cDestiny', this.value );" placeholder="Ziel-URL" value="{if isset($cPost_arr.cDestiny)}{$cPost_arr.cDestiny}{/if}" />
                            <div class="input-group-btn" style="min-width:100%;display:block;top:100%;">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></button>
                                <ul class="dropdown-menu" style="min-width:100%;" id="resSearchcDestiny"></ul>
                            </div>
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