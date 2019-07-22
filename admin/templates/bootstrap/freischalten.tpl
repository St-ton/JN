{config_load file="$lang.conf" section='freischalten'}
{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('freischalten') cBeschreibung=__('freischaltenDesc') cDokuURL=__('freischaltenURL')}
<div id="content">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 col-sm-4">
                    {include file='tpl_inc/language_switcher.tpl' id='formSprachwechselSelect' action='freischalten.php"'}
                </div>
                <div class="col-md-8 col-sm-8">
                    <form name="suche" method="post" action="freischalten.php">
                        <div class="row">
                            {$jtl_token}
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="search_type">{__('freischaltenSearchType')}:</label>
                                    </span>
                                    <span class="label-wrap">
                                        <select class="custom-select" name="cSuchTyp" id="search_type">
                                            <option value="Bewertung"{if isset($cSuchTyp) && $cSuchTyp === 'Bewertung'} selected{/if}>{__('reviews')}</option>
                                            <option value="Livesuche"{if isset($cSuchTyp) && $cSuchTyp === 'Livesuche'} selected{/if}>{__('freischaltenLivesearch')}</option>
                                            <option value="Newskommentar"{if isset($cSuchTyp) && $cSuchTyp === 'Newskommentar'} selected{/if}>{__('freischaltenNewsComments')}</option>
                                            <option value="Newsletterempfaenger"{if isset($cSuchTyp) && $cSuchTyp === 'Newsletterempfaenger'} selected{/if}>{__('freischaltenNewsletterReceiver')}</option>
                                        </select>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <input type="hidden" name="Suche" value="1" />
                                <div class="input-group">
                                    <label for="search_key" class="sr-only">{__('freischaltenSearchItem')}</label>
                                    <span class="label-wrap">
                                        <input class="form-control" name="cSuche" type="text" value="{if isset($cSuche)}{$cSuche}{/if}"
                                               id="search_key" placeholder="{__('freischaltenSearchItem')}">
                                    </span>
                                    <button name="submitSuche" type="submit" class="btn btn-primary ml-1"><i class="fal fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if !isset($cTab) || empty($cTab) || $cTab === 'bewertungen'} active{/if}" data-toggle="tab" role="tab" href="#bewertungen">
                        {__('reviews')} <span class="badge">{$oPagiBewertungen->getItemCount()}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'livesearch'} active{/if}" data-toggle="tab" role="tab" href="#livesearch">
                        {__('freischaltenLivesearch')} <span class="badge">{$oPagiSuchanfragen->getItemCount()}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'newscomments'} active{/if}" data-toggle="tab" role="tab" href="#newscomments">
                        {__('freischaltenNewsComments')} <span class="badge">{$oPagiNewskommentare->getItemCount()}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'newsletter'} active{/if}" data-toggle="tab" role="tab" href="#newsletter">
                        {__('freischaltenNewsletterReceiver')} <span class="badge">{$oPagiNewsletterEmpfaenger->getItemCount()}</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="bewertungen" class="tab-pane fade {if !isset($cTab) || empty($cTab) || $cTab === 'bewertungen'} active show{/if}">
                {if $oBewertung_arr|@count > 0 && $oBewertung_arr}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiBewertungen cAnchor='bewertungen'}
                    <form method="post" action="freischalten.php">
                        {$jtl_token}
                        <input type="hidden" name="freischalten" value="1" />
                        <input type="hidden" name="bewertungen" value="1" />
                        <input type="hidden" name="tab" value="bewertungen" />
                        <div>
                            <div class="subheading1">{__('reviews')}</div>
                            <hr class="mb-3">
                            <div class="table-responsive">
                                <table class="list table">
                                    <thead>
                                    <tr>
                                        <th class="check"></th>
                                        <th class="tleft">{__('product')}</th>
                                        <th class="tleft">{__('freischaltenReviewsCustomer')}</th>
                                        <th>{__('stars')}</th>
                                        <th>{__('freischaltenReviewsDate')}</th>
                                        <th>{__('actions')}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $oBewertung_arr as $oBewertung}
                                        <tr>
                                            <td class="check">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" name="kBewertung[]" type="checkbox" id="review-id-{$oBewertung->kBewertung}" value="{$oBewertung->kBewertung}" />
                                                    <label class="custom-control-label" for="review-id-{$oBewertung->kBewertung}"></label>
                                                </div>
                                                <input type="hidden" name="kArtikel[]" value="{$oBewertung->kArtikel}" />
                                                <input type="hidden" name="kBewertungAll[]" value="{$oBewertung->kBewertung}" />
                                            </td>
                                            <td><a href="{$shopURL}/index.php?a={$oBewertung->kArtikel}" target="_blank">{$oBewertung->ArtikelName}</a></td>
                                            <td>{$oBewertung->cName}.</td>
                                            <td class="tcenter">{$oBewertung->nSterne}</td>
                                            <td class="tcenter">{$oBewertung->Datum}</td>
                                            <td class="tcenter">
                                                <a class="btn btn-default btn-sm" title="{__('modify')}"
                                                   href="bewertung.php?a=editieren&kBewertung={$oBewertung->kBewertung}&nFZ=1&token={$smarty.session.jtl_token}">
                                                    <i class="fal fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td colspan="6">
                                                <strong>{$oBewertung->cTitel}</strong>
                                                <p>{$oBewertung->cText}</p>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            <div class="save-wrapper">
                                <div class="row">
                                    <div class="col-sm-6 col-xl-auto text-left mb-3">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS1" type="checkbox" onclick="AllMessages(this.form);" />
                                            <label class="custom-control-label" for="ALLMSGS1">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="freischaltenleoschen" type="submit" class="btn btn-danger btn-block mb-3">
                                            <i class="fas fa-trash-alt"></i> {__('deleteSelected')}
                                        </button>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto">
                                        <button name="freischaltensubmit" type="submit" class="btn btn-primary btn-block">
                                            <i class="fa fa-thumbs-up"></i> {__('unlockMarked')}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="livesearch" class="tab-pane fade {if isset($cTab) && $cTab === 'livesearch'} active show{/if}">
                {if $oSuchanfrage_arr|@count > 0 && $oSuchanfrage_arr}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiSuchanfragen cAnchor='livesearch'}
                    <div>
                        <form method="post" action="freischalten.php">
                            {$jtl_token}
                            <input type="hidden" name="freischalten" value="1" />
                            <input type="hidden" name="suchanfragen" value="1" />
                            <input type="hidden" name="tab" value="livesearch" />
                            {if isset($nSort)}
                            <input type="hidden" name="nSort" value="{$nSort}" />
                            {/if}
                            {if isset($cSuche) && isset($cSuchTyp) && $cSuche && $cSuchTyp}
                                {assign var=cSuchStr value='Suche=1&cSuche='|cat:$cSuche|cat:'&cSuchTyp='|cat:$cSuchTyp|cat:'&'}
                            {else}
                                {assign var=cSuchStr value=''}
                            {/if}
                            <div class="table-responsive">
                                <table class="list table table-striped">
                                    <thead>
                                    <tr>
                                        <th class="check">&nbsp;</th>
                                        <th class="tleft">(<a href="freischalten.php?tab=livesearch&{$cSuchStr}nSort=1{if !isset($nSort) || $nSort != 11}1{/if}&token={$smarty.session.jtl_token}" style="text-decoration: underline;">{if !isset($nSort) || $nSort != 11}Z...A{else}A...Z{/if}</a>) {__('freischaltenLivesearchSearch')}</th>
                                        <th>(<a href="freischalten.php?tab=livesearch&{$cSuchStr}nSort=2{if !isset($nSort) || $nSort != 22}2{/if}&token={$smarty.session.jtl_token}" style="text-decoration: underline;">{if !isset($nSort) || $nSort != 22}1...9{else}9...1{/if}</a>) {__('freischaltenLivesearchCount')}</th>
                                        <th>(<a href="freischalten.php?tab=livesearch&{$cSuchStr}nSort=3{if !isset($nSort) || $nSort != 33}3{/if}&token={$smarty.session.jtl_token}" style="text-decoration: underline;">{if !isset($nSort) || $nSort != 33}0...1{else}1...0{/if}</a>) {__('freischaltenLivesearchHits')}</th>
                                        <th>{__('freischaltenLiveseachDate')}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $oSuchanfrage_arr as $oSuchanfrage}
                                        <tr>
                                            <td class="check">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" name="kSuchanfrage[]" type="checkbox" id="search-request-id-{$oSuchanfrage->kSuchanfrage}" value="{$oSuchanfrage->kSuchanfrage}" />
                                                    <label class="custom-control-label" for="search-request-id-{$oSuchanfrage->kSuchanfrage}"></label>
                                                </div>
                                            </td>
                                            <td class="tleft">{$oSuchanfrage->cSuche}</td>
                                            <td class="tcenter">{$oSuchanfrage->nAnzahlGesuche}</td>
                                            <td class="tcenter">{$oSuchanfrage->nAnzahlTreffer}</td>
                                            <td class="tcenter">{$oSuchanfrage->dZuletztGesucht_de}</td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td class="check">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input"  name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);" />
                                                <label class="custom-control-label" for="ALLMSGS2"></label>
                                            </div>
                                        </td>
                                        <td colspan="5"><label for="ALLMSGS2">{__('globalSelectAll')}</label></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="save-wrapper">
                                <div class="row">
                                    <div class="col-sm-6 col-xl-auto mb-3">
                                        <div class="input-group" data-toggle="tooltip" data-placement="bottom" title='{__('freischaltenMappingDesc')}'>
                                            <span class="input-group-addon">
                                                <label for="cMapping">{__('linkMarked')}:</label>
                                            </span>
                                            <input class="form-control" name="cMapping" id="cMapping" type="text" value="" />
                                            <span class="input-group-btn ml-1">
                                                <button name="submitMapping" type="submit" value="Verknüpfen" class="btn btn-primary">{__('linkVerb')}</button>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto">
                                        <button name="freischaltenleoschen" type="submit" value="Markierte löschen" class="btn btn-danger btn-block mb-3">
                                            <i class="fas fa-trash-alt"></i> {__('deleteSelected')}
                                        </button>
                                    </div>
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="freischaltensubmit" type="submit" value="Markierte freischalten" class="btn btn-primary btn-block">
                                            <i class="fa fa-thumbs-up"></i> {__('unlockMarked')}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="newscomments" class="tab-pane fade {if isset($cTab) && $cTab === 'newscomments'} active show{/if}">
                {if $oNewsKommentar_arr|@count > 0 && $oNewsKommentar_arr}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiNewskommentare cAnchor='newscomments'}
                    <div>
                        <form method="post" action="freischalten.php">
                            {$jtl_token}
                            <input type="hidden" name="freischalten" value="1" />
                            <input type="hidden" name="newskommentare" value="1" />
                            <input type="hidden" name="tab" value="newscomments" />
                            <div class="table-responsive">
                                <table class="list table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="check">&nbsp;</th>
                                            <th class="tleft">{__('visitor')}</th>
                                            <th class="tleft">{__('text')}</th>
                                            <th>{__('freischaltenNewsCommentsDate')}</th>
                                            <th>{__('actions')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach $oNewsKommentar_arr as $oNewsKommentar}
                                            <tr>
                                                <td class="check">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input" type="checkbox" name="kNewsKommentar[]" id="ncid-{$oNewsKommentar->kNewsKommentar}" value="{$oNewsKommentar->kNewsKommentar}" />
                                                        <label class="custom-control-label" for="ncid-{$oNewsKommentar->kNewsKommentar}"></label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <label for="ncid-{$oNewsKommentar->kNewsKommentar}">
                                                        {if $oNewsKommentar->cVorname|strlen > 0}
                                                            {$oNewsKommentar->cVorname} {$oNewsKommentar->cNachname}
                                                        {else}
                                                            {$oNewsKommentar->cName}
                                                        {/if}
                                                    </label>
                                                </td>
                                                <td>{$oNewsKommentar->cBetreff|truncate:50:'...'}</td>
                                                <td class="tcenter">{$oNewsKommentar->dErstellt_de}</td>
                                                <td class="tcenter">
                                                    <a class="btn btn-default btn-sm" title="{__('modify')}"
                                                       href="news.php?news=1&kNews={$oNewsKommentar->kNews}&kNewsKommentar={$oNewsKommentar->kNewsKommentar}&nkedit=1&nFZ=1&token={$smarty.session.jtl_token}">
                                                        <i class="fal fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td class="check">

                                            </td>
                                            <td colspan="5"><label for="ALLMSGS4"></label></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="save-wrapper">
                                <div class="row">
                                    <div class="col-sm-6 col-xl-auto text-left mb-3">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS4" type="checkbox" onclick="AllMessages(this.form);" />
                                            <label class="custom-control-label" for="ALLMSGS4">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="freischaltenleoschen" type="submit" value="Markierte löschen" class="btn btn-danger btn-block mb-3">
                                            <i class="fas fa-trash-alt"></i> {__('deleteSelected')}
                                        </button>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto">
                                        <button name="freischaltensubmit" type="submit" value="Markierte freischalten" class="btn btn-primary btn-block">
                                            <i class="fa fa-thumbs-up"></i> {__('unlockMarked')}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="newsletter" class="tab-pane fade {if isset($cTab) && $cTab === 'newsletter'} active show{/if}">
                {if $oNewsletterEmpfaenger_arr|@count > 0 && $oNewsletterEmpfaenger_arr}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiNewsletterEmpfaenger cAnchor='newsletter'}
                    <div>
                        <form method="post" action="freischalten.php">
                            {$jtl_token}
                            <input type="hidden" name="freischalten" value="1" />
                            <input type="hidden" name="newsletterempfaenger" value="1" />
                            <input type="hidden" name="tab" value="newsletter" />
                            {if isset($nSort)}
                                <input type="hidden" name="nSort" value="{$nSort}" />
                            {/if}
                            <div class="table-responsive">
                                <table class="list table">
                                    <thead>
                                        <tr>
                                            <th class="check">&nbsp;</th>
                                            <th class="tleft">{__('email')}</th>
                                            <th class="tleft">{__('firstName')}</th>
                                            <th class="tleft">{__('lastName')}</th>
                                            <th>(<a href="freischalten.php?tab=newsletter&{$cSuchStr}nSort=4{if !isset($nSort) || $nSort != 44}4{/if}&token={$smarty.session.jtl_token}">{if !isset($nSort) || $nSort != 44}{__('old')}...{__('new')}{elseif isset($nSort) && $nSort == 44}{__('new')}...{__('old')}{/if}</a>) {__('date')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach $oNewsletterEmpfaenger_arr as $oNewsletterEmpfaenger}
                                            <tr>
                                                <td class="check">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input" type="checkbox" name="kNewsletterEmpfaenger[]" id="newsletter-recipient-id-{$oNewsletterEmpfaenger->kNewsletterEmpfaenger}" value="{$oNewsletterEmpfaenger->kNewsletterEmpfaenger}" />
                                                        <label class="custom-control-label" for="newsletter-recipient-id-{$oNewsletterEmpfaenger->kNewsletterEmpfaenger}"></label>
                                                    </div>
                                                </td>
                                                <td>{$oNewsletterEmpfaenger->cEmail}</td>
                                                <td>{$oNewsletterEmpfaenger->cVorname}</td>
                                                <td>{$oNewsletterEmpfaenger->cNachname}</td>
                                                <td class="tcenter">{$oNewsletterEmpfaenger->dEingetragen_de}</td>
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            <div class="save-wrapper">
                                <div class="row">
                                    <div class="col-sm-6 col-xl-auto text-left mb-3">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS5" type="checkbox" onclick="AllMessages(this.form);" />
                                            <label class="custom-control-label" for="ALLMSGS5">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="freischaltenleoschen" type="submit" value="Markierte löschen" class="btn btn-danger btn-block mb-3">
                                            <i class="fas fa-trash-alt"></i> {__('deleteSelected')}
                                        </button>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto">
                                        <button name="freischaltensubmit" type="submit" value="Markierte freischalten" class="btn btn-primary btn-block">
                                            <i class="fa fa-thumbs-up"></i> {__('unlockMarked')}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
