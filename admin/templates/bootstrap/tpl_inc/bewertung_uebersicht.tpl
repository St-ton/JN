{include file='tpl_inc/seite_header.tpl' cTitel=__('votesystem') cBeschreibung=__('votesystemDesc') cDokuURL=__('votesystemURL')}
<div id="content" class="container-fluid">
    <div class="block">
        {include file='tpl_inc/language_switcher.tpl' action='bewertung.php'}
    </div>
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'freischalten'} active{/if}">
            <a data-toggle="tab" role="tab" href="#freischalten">{__('ratingsInaktive')}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'letzten50'} active{/if}">
            <a data-toggle="tab" role="tab" href="#letzten50">{__('ratingLast50')}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'artikelbewertung'} active{/if}">
            <a data-toggle="tab" role="tab" href="#artikelbewertung">{__('ratingForProduct')}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einstellungen">{__('settings')}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="freischalten" class="tab-pane fade {if !isset($cTab) || $cTab === 'freischalten'} active show{/if}">
            {if $inactiveReviews|count > 0}
                {include file='tpl_inc/pagination.tpl' pagination=$oPagiInaktiv cAnchor='freischalten'}
                <form method="post" action="bewertung.php">
                    {$jtl_token}
                    <input type="hidden" name="bewertung_nicht_aktiv" value="1" />
                    <input type="hidden" name="tab" value="freischalten" />
                    <div class="card">
                        <div class="card-header">
                            <div class="subheading1">{__('ratingsInaktive')}</div>
                            <hr class="mb-n3">
                        </div>
                        <div class="table-responsive card-body">
                            <table  class="table table-striped">
                                <thead>
                                <tr>
                                    <th class="check">&nbsp;</th>
                                    <th class="tleft">{__('productName')}</th>
                                    <th class="tleft">{__('customerName')}</th>
                                    <th class="tleft">{__('ratingText')}</th>
                                    <th class="th-5">{__('ratingStars')}</th>
                                    <th class="th-6">{__('date')}</th>
                                    <th class="th-7">&nbsp;</th>
                                </tr>
                                </thead>
                                <tbody>
                                    {foreach $inactiveReviews as $review}
                                        <tr>
                                            <td class="check">
                                                <input type="hidden" name="kArtikel[{$review@index}]" value="{$review->kArtikel}"/>
                                                <input name="kBewertung[{$review@index}]" type="checkbox" value="{$review->kBewertung}" id="inactive-{$review->kBewertung}" />
                                            </td>
                                            <td>
                                                <label for="inactive-{$review->kBewertung}">{$review->ArtikelName}</label>
                                                &nbsp;<a href="{$shopURL}/index.php?a={$review->kArtikel}" target="_blank"><i class="fas fa fa-external-link"></i></a>
                                            </td>
                                            <td>{$review->cName}.</td>
                                            <td><b>{$review->cTitel}</b><br />{$review->cText}</td>
                                            <td class="tcenter">{$review->nSterne}</td>
                                            <td class="tcenter">{$review->Datum}</td>
                                            <td class="tcenter">
                                                <a href="bewertung.php?a=editieren&kBewertung={$review->kBewertung}&tab=freischalten&token={$smarty.session.jtl_token}"
                                                   class="btn btn-default" title="{__('modify')}">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td class="check"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
                                        <td colspan="6"><label for="ALLMSGS">{__('globalSelectAll')}</label></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="card-footer">
                            <div class="btn-group">
                                <button name="aktivieren" type="submit" value="{__('activate')}" class="btn btn-primary"><i class="fa fa-thumbs-up"></i> {__('activate')}</button>
                                <button name="loeschen" type="submit" value="{__('delete')}" class="btn btn-danger"><i class="fa fa-trash"></i> {__('delete')}</button>
                            </div>
                        </div>
                    </div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
            {/if}
        </div>
        <div id="letzten50" class="tab-pane fade {if isset($cTab) && $cTab === 'letzten50'} active show{/if}">
            {if $activeReviews|count > 0}
                {include file='tpl_inc/pagination.tpl' pagination=$oPagiAktiv cAnchor='letzten50'}
                <form name="letzten50" method="post" action="bewertung.php">
                    {$jtl_token}
                    <input type="hidden" name="bewertung_aktiv" value="1" />
                    <input type="hidden" name="tab" value="letzten50" />
                    <div class="card">
                        <div class="card-header">
                            <div class="subheading1">{__('ratingLast50')}</div>
                            <hr class="mb-n3">
                        </div>
                        <div class="table-responsive card-body">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th class="check">&nbsp;</th>
                                    <th class="tleft">{__('productName')}</th>
                                    <th class="tleft">{__('customerName')}</th>
                                    <th class="tleft">{__('ratingText')}</th>
                                    <th class="th-5">{__('ratingStars')}</th>
                                    <th class="th-6">{__('date')}</th>
                                    <th class="th-7">&nbsp;</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $activeReviews as $review}
                                    <tr>
                                        <td class="check">
                                            <input name="kBewertung[]" type="checkbox" value="{$review->kBewertung}" id="l50-{$review->kBewertung}">
                                            <input type="hidden" name="kArtikel[]" value="{$review->kArtikel}">
                                        </td>
                                        <td>
                                            <label for="l50-{$review->kBewertung}">{$review->ArtikelName}</label>
                                            &nbsp;<a href="{$shopURL}/index.php?a={$review->kArtikel}" target="_blank"><i class="fas fa fa-external-link"></i></a>
                                        </td>
                                        <td>{$review->cName}.</td>
                                        <td>
                                            <strong>{$review->cTitel}</strong><br>
                                            {$review->cText}
                                            {if !empty($review->cAntwort)}
                                                <blockquote class="review-reply">
                                                    <strong>{__('ratingReply')}</strong><br>
                                                    {$review->cAntwort}
                                                </blockquote>
                                            {/if}
                                        </td>
                                        <td class="tcenter">{$review->nSterne}</td>
                                        <td class="tcenter">{$review->Datum}</td>
                                        <td class="tcenter7 tright" style="min-width: 100px;">
                                            <div class="btn-group">
                                                {if !empty($review->cAntwort)}
                                                    <a href="bewertung.php?a=delreply&kBewertung={$review->kBewertung}&tab=letzten50&token={$smarty.session.jtl_token}"
                                                       class="btn btn-danger" title="{__('removeReply')}">
                                                        <i class="fa fa-times-circle-o"></i>
                                                    </a>
                                                {/if}
                                                <a href="bewertung.php?a=editieren&kBewertung={$review->kBewertung}&tab=letzten50&token={$smarty.session.jtl_token}"
                                                   class="btn btn-default" title="{__('modify')}">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td class="check"><input name="ALLMSGS" id="ALLMSGS3" type="checkbox" onclick="AllMessages(this.form);"></td>
                                    <td colspan="6"><label for="ALLMSGS3">{__('globalSelectAll')}</label></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="card-footer">
                            <button name="loeschen" type="submit" value="{__('delete')}" class="btn btn-danger"><i class="fa fa-trash"></i> {__('deleteSelected')}</button>
                        </div>
                    </div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
            {/if}
        </div>
        <div id="artikelbewertung" class="tab-pane fade {if isset($cTab) && $cTab === 'artikelbewertung'} active show{/if}">
            <form name="artikelbewertung" method="post" action="bewertung.php">
                <div class="card">
                    <div class="card-body">
                    {$jtl_token}
                    <div class="input-group col-xs-6" style="float: none;">
                        <span class="input-group-addon">
                            <label for="content">{__('ratingcArtNr')}:</label>
                        </span>
                        <input type="hidden" name="bewertung_aktiv" value="1" />
                        <input type="hidden" name="tab" value="artikelbewertung" />
                        <input class="form-control" name="cArtNr" type="text" value="{$cArtNr|default:''}" />
                        <span class="input-group-prepend">
                            <button name="submitSearch" type="submit" value="{__('search')}" class="btn btn-info"><i class="fa fa-search"></i> {__('search')}</button>
                        </span>
                    </div>
                    {if isset($cArtNr) && $cArtNr|strlen > 0}
                        <div class="alert alert-info">{__('ratingSearchedFor')}: {$cArtNr}</div>
                    {/if}
                    {if !(isset($filteredReviews) && $filteredReviews|@count > 0)}
                        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                    {/if}
                    </div>
                </div>
                {if isset($filteredReviews) && $filteredReviews|@count > 0}
                    <div class="card">
                        <div class="card-header">
                            <div class="subheading1">{$cArtNr}</div>
                            <hr class="mb-n3">
                        </div>
                        <div class="table-responsive card-body">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th class="th-1">&nbsp;</th>
                                    <th class="tleft">{__('productName')}</th>
                                    <th class="tleft">{__('customerName')}</th>
                                    <th class="tleft">{__('ratingText')}</th>
                                    <th class="th-5">{__('ratingStars')}</th>
                                    <th class="th-6">{__('date')}</th>
                                    <th class="th-7">&nbsp;</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $filteredReviews as $review}
                                    <tr>
                                        <td>
                                            <input name="kBewertung[]" type="checkbox" value="{$review->kBewertung}" id="filtered-{$review->kBewertung}">
                                            <input type="hidden" name="kArtikel[]" value="{$review->kArtikel}">
                                        </td>
                                        <td>
                                            <label for="filtered-{$review->kBewertung}">{$review->ArtikelName}</label>
                                            &nbsp;<a href="{$shopURL}/index.php?a={$review->kArtikel}" target="_blank"><i class="fas fa fa-external-link"></i></a>
                                        </td>
                                        <td>{$review->cName}.</td>
                                        <td><b>{$review->cTitel}</b><br />{$review->cText}</td>
                                        <td class="tcenter">{$review->nSterne}</td>
                                        <td class="tcenter">{$review->Datum}</td>
                                        <td class="tcenter">
                                            <a href="bewertung.php?a=editieren&kBewertung={$review->kBewertung}&tab=artikelbewertung"
                                               class="btn btn-default" title="{__('modify')}">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td><input name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);"></td>
                                    <td colspan="6"><label for="ALLMSGS2">{__('globalSelectAll')}</label></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="card-footer">
                            <button name="loeschen" type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {__('delete')}</button>
                        </div>
                    </div>
                {/if}
            </form>
        </div>
        <div id="einstellungen" class="tab-pane fade {if isset($cTab) && $cTab === 'einstellungen'} active show{/if}">
            <form name="einstellen" method="post" action="bewertung.php">
                {$jtl_token}
                <input type="hidden" name="einstellungen" value="1" />
                <input type="hidden" name="tab" value="einstellungen" />
                <div class="settings card">
                    <div class="card-header">
                        <span class="subheading1">{__('settings')}</span>
                        <hr class="mb-n3">
                    </div>
                    <div class="card-body">
                        {foreach $oConfig_arr as $oConfig}
                            {if $oConfig->cConf === 'Y'}
                                <div class="form-group form-row align-items-center">
                                    <label class="col col-sm-4 col-form-label text-sm-right" for="{$oConfig->cWertName}">{$oConfig->cName}
                                        {if $oConfig->cWertName|strpos:'_guthaben'} <span id="EinstellungAjax_{$oConfig->cWertName}"></span>{/if}:
                                    </label>
                                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                        {if $oConfig->cInputTyp === 'selectbox'}
                                            <select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" class="custom-select combo">
                                                {foreach $oConfig->ConfWerte as $wert}
                                                    <option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                                {/foreach}
                                            </select>
                                        {elseif $oConfig->cInputTyp === 'listbox'}
                                            <select name="{$oConfig->cWertName}[]" id="{$oConfig->cWertName}" multiple="multiple" class="custom-select combo">
                                                {foreach $oConfig->ConfWerte as $wert}
                                                    <option value="{$wert->kKundengruppe}" {foreach $oConfig->gesetzterWert as $gesetzterWert}{if $gesetzterWert->cWert == $wert->kKundengruppe}selected{/if}{/foreach}>{$wert->cName}</option>
                                                {/foreach}
                                            </select>
                                        {elseif $oConfig->cInputTyp === 'number'}
                                            <input class="form-control" type="number" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1"{if $oConfig->cWertName|strpos:"_guthaben"} onKeyUp="setzePreisAjax(false, 'EinstellungAjax_{$oConfig->cWertName}', this);"{/if} />
                                        {else}
                                            <input class="form-control" type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1"{if $oConfig->cWertName|strpos:"_guthaben"} onKeyUp="setzePreisAjax(false, 'EinstellungAjax_{$oConfig->cWertName}', this);"{/if} />
                                        {/if}
                                    </div>
                                    {if $oConfig->cBeschreibung}
                                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=$oConfig->cBeschreibung cID=$oConfig->kEinstellungenConf}</div>
                                    {/if}
                                </div>
                            {else}
                                {if $oConfig->cBeschreibung}
                                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                        {getHelpDesc cDesc=$oConfig->cBeschreibung cID=$oConfig->kEinstellungenConf}
                                    </div>
                                {/if}
                            {/if}
                        {/foreach}
                    </div>
                    <div class="card-footer">
                        <button type="submit" value="{__('save')}" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    {foreach $oConfig_arr as $oConfig}
        {if $oConfig->cWertName|strpos:'_guthaben'}
            ioCall('getCurrencyConversion', [0, $('#{$oConfig->cWertName}').val(), 'EinstellungAjax_{$oConfig->cWertName}']);
        {/if}
    {/foreach}
</script>
