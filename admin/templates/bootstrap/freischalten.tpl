{config_load file="$lang.conf" section="freischalten"}
{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=#freischalten# cBeschreibung=#freischaltenDesc# cDokuURL=#freischaltenURL#}
<div id="content" class="container-fluid">
    <div class="block container2 clearall">
        <div class="left p50">
            <form name="sprache" method="post" action="freischalten.php">
                {$jtl_token}
                <input type="hidden" name="sprachwechsel" value="1" />
                <div class="input-group p50">
                    <span class="input-group-addon">
                        <label for="{#changeLanguage#}">{#changeLanguage#}</label>
                    </span>
                    <span class="input-group-wrap last">
                        <select class="form-control" id="{#changeLanguage#}" name="kSprache" onchange="document.sprache.submit();">
                            {foreach name=sprachen from=$Sprachen item=sprache}
                            <option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
                            {/foreach}
                        </select>
                    </span>
                </div>
            </form>
        </div>
        <div class="right tright p50">
            <form name="suche" method="post" action="freischalten.php">
                {$jtl_token}
                <div class="input-group p50 left" style="padding-right:10px;">
                    <span class="input-group-addon">
                        <label for="search_type">{#freischaltenSearchType#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <select class="form-control" name="cSuchTyp" id="search_type">
                            <option value="Bewertung"{if isset($cSuchTyp) && $cSuchTyp === 'Bewertung'} selected{/if}>{#freischaltenReviews#}</option>
                            <option value="Livesuche"{if isset($cSuchTyp) && $cSuchTyp === 'Livesuche'} selected{/if}>{#freischaltenLivesearch#}</option>
                            <option value="Tag"{if isset($cSuchTyp) && $cSuchTyp === 'Tag'} selected{/if}>{#freischaltenTags#}</option>
                            <option value="Newskommentar"{if isset($cSuchTyp) && $cSuchTyp === 'Newskommentar'} selected{/if}>{#freischaltenNewsComments#}</option>
                            <option value="Newsletterempfaenger"{if isset($cSuchTyp) && $cSuchTyp === 'Newsletterempfaenger'} selected{/if}>{#freischaltenNewsletterReceiver#}</option>
                        </select>
                    </span>
                </div>
                <input type="hidden" name="Suche" value="1" />
                <div class="input-group p50 right" style="margin-left:0;">
                    <span class="input-group-addon">
                        <label for="search_key">{#freischaltenSearchItem#}</label>
                    </span>
                    <input class="form-control" name="cSuche" type="text" value="{if isset($cSuche)}{$cSuche}{/if}" id="search_key" />
                    <span class="input-group-btn">
                        <button name="submitSuche" type="submit" class="btn btn-primary"><i class="fa fa-search"></i> {#freischaltenSearchBTN#}</button>
                    </span>
                </div>
            </form>
        </div>
    </div>

    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'bewertungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#bewertungen">{#freischaltenReviews#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'livesearch'} active{/if}">
            <a data-toggle="tab" role="tab" href="#livesearch">{#freischaltenLivesearch#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'tags'} active{/if}">
            <a data-toggle="tab" role="tab" href="#tags">{#freischaltenTags#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'newscomments'} active{/if}">
            <a data-toggle="tab" role="tab" href="#newscomments">{#freischaltenNewsComments#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'newsletter'} active{/if}">
            <a data-toggle="tab" role="tab" href="#newsletter">{#freischaltenNewsletterReceiver#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="bewertungen" class="tab-pane fade {if !isset($cTab) || $cTab === 'bewertungen'} active in{/if}">
            {if $oBewertung_arr|@count > 0 && $oBewertung_arr}
                {include file='pagination.tpl' cSite=1 cUrl='freischalten.php' oBlaetterNavi=$oBlaetterNaviBewertungen cParams='' hash='#bewertungen'}
                <form method="post" action="freischalten.php">
                    {$jtl_token}
                    <input type="hidden" name="freischalten" value="1" />
                    <input type="hidden" name="bewertungen" value="1" />
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{#freischaltenReviews#}</h3>
                        </div>
                        <table class="list table">
                            <thead>
                            <tr>
                                <th class="check"></th>
                                <th class="tleft">{#freischaltenReviewsProduct#}</th>
                                <th class="tleft">{#freischaltenReviewsCustomer#}</th>
                                <th>{#freischaltenReviewsStars#}</th>
                                <th>{#freischaltenReviewsDate#}</th>
                                <th>Aktionen</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach name=bewertungen from=$oBewertung_arr item=oBewertung}
                                <tr>
                                    <td class="check">
                                        <input name="kBewertung[]" type="checkbox" value="{$oBewertung->kBewertung}" />
                                        <input type="hidden" name="kArtikel[]" value="{$oBewertung->kArtikel}" />
                                        <input type="hidden" name="kBewertungAll[]" value="{$oBewertung->kBewertung}" />
                                    </td>
                                    <td><a href="../../index.php?a={$oBewertung->kArtikel}" target="_blank">{$oBewertung->ArtikelName}</a></td>
                                    <td>{$oBewertung->cName}.</td>
                                    <td class="tcenter">{$oBewertung->nSterne}</td>
                                    <td class="tcenter">{$oBewertung->Datum}</td>
                                    <td class="tcenter">
                                        <a class="btn btn-default btn-sm" href="bewertung.php?a=editieren&kBewertung={$oBewertung->kBewertung}&nFZ=1&token={$smarty.session.jtl_token}"><i class="fa fa-edit"></i></a>
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
                            <tfoot>
                            <tr>
                                <td class="check"><input name="ALLMSGS" id="ALLMSGS1" type="checkbox" onclick="AllMessages(this.form);" /></td>
                                <td colspan="5"><label for="ALLMSGS1">{#freischaltenSelectAll#}</label></td>
                            </tr>
                            </tfoot>
                        </table>
                        <div class="panel-footer">
                            <div class="btn-group">
                                <button name="freischaltensubmit" type="submit" class="btn btn-primary"><i class="fa fa-thumbs-up"></i> Markierte freischalten</button>
                                <button name="freischaltenleoschen" type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> Markierte l&ouml;schen</button>
                            </div>
                        </div>
                    </div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="livesearch" class="tab-pane fade {if isset($cTab) && $cTab === 'livesearch'} active in{/if}">
            {if $oSuchanfrage_arr|@count > 0 && $oSuchanfrage_arr}
                {include file='pagination.tpl' cSite=2 cUrl='freischalten.php' oBlaetterNavi=$oBlaetterNaviSuchanfrage cParams='' hash='#livesearch'}
                <form method="post" action="freischalten.php">
                    {$jtl_token}
                    <input type="hidden" name="freischalten" value="1" />
                    <input type="hidden" name="suchanfragen" value="1" />

                    {if isset($cSuche) && isset($cSuchTyp) && $cSuche && $cSuchTyp}
                        {assign var=cSuchStr value="Suche=1&cSuche="|cat:$cSuche|cat:"&cSuchTyp="|cat:$cSuchTyp|cat:"&"}
                    {else}
                        {assign var=cSuchStr value=""}
                    {/if}

                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="check">&nbsp;</th>
                            <th class="tleft">(<a href="freischalten.php?{$cSuchStr}nSort=1{if !isset($nSort) || $nSort != 11}1{/if}{if isset($oBlaetterNaviSuchanfragen->nAktuelleSeite) && $oBlaetterNaviSuchanfragen->nAktuelleSeite > 0}&s1={$oBlaetterNaviSuchanfragen->nAktuelleSeite}{/if}&token={$smarty.session.jtl_token}" style="text-decoration: underline;">{if !isset($nSort) || $nSort != 11}Z...A{else}A...Z{/if}</a>) {#freischaltenLivesearchSearch#}</th>
                            <th>(<a href="freischalten.php?{$cSuchStr}nSort=2{if !isset($nSort) || $nSort != 22}2{/if}{if isset($oBlaetterNaviSuchanfragen->nAktuelleSeite) && $oBlaetterNaviSuchanfragen->nAktuelleSeite > 0}&s1={$oBlaetterNaviSuchanfragen->nAktuelleSeite}{/if}&token={$smarty.session.jtl_token}" style="text-decoration: underline;">{if !isset($nSort) || $nSort != 22}1...9{else}9...1{/if}</a>) {#freischaltenLivesearchCount#}</th>
                            <th>(<a href="freischalten.php?{$cSuchStr}nSort=3{if !isset($nSort) || $nSort != 33}3{/if}{if isset($oBlaetterNaviSuchanfragen->nAktuelleSeite) && $oBlaetterNaviSuchanfragen->nAktuelleSeite > 0}&s1={$oBlaetterNaviSuchanfragen->nAktuelleSeite}{/if}&token={$smarty.session.jtl_token}" style="text-decoration: underline;">{if !isset($nSort) || $nSort != 33}0...1{else}1...0{/if}</a>) {#freischaltenLivesearchHits#}</th>
                            <th>{#freischaltenLiveseachDate#}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach name=suchanfragen from=$oSuchanfrage_arr item=oSuchanfrage}
                            <tr class="tab_bg{$smarty.foreach.suchanfragen.iteration%2}">
                                <td class="check"><input name="kSuchanfrage[]" type="checkbox" value="{$oSuchanfrage->kSuchanfrage}" /></td>
                                <td class="tleft">{$oSuchanfrage->cSuche}</td>
                                <td class="tcenter">{$oSuchanfrage->nAnzahlGesuche}</td>
                                <td class="tcenter">{$oSuchanfrage->nAnzahlTreffer}</td>
                                <td class="tcenter">{$oSuchanfrage->dZuletztGesucht_de}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                        <tfoot>
                        <tr>
                            <td class="check"><input name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);" /></td>
                            <td colspan="5"><label for="ALLMSGS2">{#freischaltenSelectAll#}</label></td>
                        </tr>
                        </tfoot>
                    </table>
                    <div class="save_wrapper btn-group">
                        <button name="freischaltensubmit" type="submit" value="Markierte freischalten" class="btn btn-primary"><i class="fa fa-thumbs-up"></i> Markierte freischalten</button>
                        <button name="freischaltenleoschen" type="submit" value="Markierte l&ouml;schen" class="btn btn-danger"><i class="fa fa-trash"></i> Markierte l&ouml;schen</button>
                        <div class="input-group" style="width: 400px;">
                            <span class="input-group-addon">
                                <input id="nMapping" name="nMapping" type="radio" value="1" /> <label for="nMapping">Markierte verkn&uuml;pfen mit</label>
                            </span>
                            <input class="form-control" name="cMapping" id="cMapping" type="text" value="" />
                            <span class="input-group-btn">
                                <button name="submitMapping" type="submit" value="Verkn&uouml;pfen" class="btn btn-primary">Verkn&uuml;pfen</button>
                            </span>
                        </div>
                    </div>
                    <div class="alert alert-info">{#freischaltenMappingDesc#}</div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="tags" class="tab-pane fade {if isset($cTab) && $cTab === 'tags'} active in{/if}">
            {if $oTag_arr|@count > 0 && $oTag_arr}
                {include file='pagination.tpl' cSite=3 cUrl='freischalten.php' oBlaetterNavi=$oBlaetterNaviTag cParams='' hash='#tags'}
                <form method="post" action="freischalten.php">
                    {$jtl_token}
                    <input type="hidden" name="freischalten" value="1" />
                    <input type="hidden" name="tags" value="1" />
                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="check">&nbsp;</th>
                            <th class="tleft">{#freischaltenTagsName#}</th>
                            <th>{#freischaltenTagsProductName#}</th>
                            <th>{#freischaltenTagsCount#}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach name=tags from=$oTag_arr item=oTag}
                            <tr>
                                <td class="check"><input name="kTag[]" type="checkbox" value="{$oTag->kTag}" /></td>
                                <td>{$oTag->cName}</td>
                                <td class="tcenter"><a href="{if isset($oTag->cArtikelSeo) && $oTag->cArtikelSeo|strlen > 0}{$shopURL}/{$oTag->cArtikelSeo}{else}{$shopURL}/index.php?a={$oTag->kArtikel}{/if}" target="_blank">{$oTag->cArtikelName}</a></td>
                                <td class="tcenter">{$oTag->Anzahl}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                        <tfoot>
                        <tr>
                            <td class="check"><input name="ALLMSGS" id="ALLMSGS3" type="checkbox" onclick="AllMessages(this.form);" /></td>
                            <td colspan="5"><label for="ALLMSGS3">{#freischaltenSelectAll#}</label></td>
                        </tr>
                        </tfoot>
                    </table>
                    <div class="save_wrapper btn-group">
                        <button name="freischaltensubmit" type="submit" value="{#freischaltenActivate#}" class="btn btn-primary"><i class="fa fa-thumbs-up"></i> Markierte freischalten</button>
                        <button name="freischaltenleoschen" type="submit" value="{#freischaltenDelete#}" class="btn btn-danger"><i class="fa fa-trash"></i> Markierte l&ouml;schen</button>
                    </div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="newscomments" class="tab-pane fade {if isset($cTab) && $cTab === 'newscomments'} active in{/if}">
            {if $oNewsKommentar_arr|@count > 0 && $oNewsKommentar_arr}
                {include file='pagination.tpl' cSite=4 cUrl='freischalten.php' oBlaetterNavi=$oBlaetterNaviTag cParams='' hash='#newscomments'}
                <form method="post" action="freischalten.php">
                    {$jtl_token}
                    <input type="hidden" name="freischalten" value="1" />
                    <input type="hidden" name="newskommentare" value="1" />
                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="check">&nbsp;</th>
                            <th class="tleft">{#freischaltenNewsCommentsVisitor#}</th>
                            <th class="tleft">{#freischaltenNewsCommentsHeadline#}</th>
                            <th>{#freischaltenNewsCommentsDate#}</th>
                            <th>Aktionen</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach name=newskommentare from=$oNewsKommentar_arr item=oNewsKommentar}
                            <tr>
                                <td class="check"><input type="checkbox" name="kNewsKommentar[]" value="{$oNewsKommentar->kNewsKommentar}" /></td>
                                <td>
                                    {if $oNewsKommentar->cVorname|count_characters > 0}
                                        {$oNewsKommentar->cVorname} {$oNewsKommentar->cNachname}
                                    {else}
                                        {$oNewsKommentar->cName}
                                    {/if}
                                </td>
                                <td>{$oNewsKommentar->cBetreff|truncate:50:"..."}</td>
                                <td class="tcenter">{$oNewsKommentar->dErstellt_de}</td>
                                <td class="tcenter">
                                    <a class="btn btn-default btn-sm" href="news.php?news=1&kNews={$oNewsKommentar->kNews}&kNewsKommentar={$oNewsKommentar->kNewsKommentar}&nkedit=1&nFZ=1&token={$smarty.session.jtl_token}" title="{#freischaltenEdit#}"><i class="fa fa-edit"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td class="check">&nbsp;</td>
                                <td char="TD8" colspan="4"><b>{$oNewsKommentar->cBetreff}</b><br />{$oNewsKommentar->cKommentar}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                        <tfoot>
                        <tr>
                            <td class="check"><input name="ALLMSGS" id="ALLMSGS4" type="checkbox" onclick="AllMessages(this.form);" /></td>
                            <td colspan="5"><label for="ALLMSGS4">{#freischaltenSelectAll#}</label></td>
                        </tr>
                        </tfoot>
                    </table>
                    <div class="save_wrapper btn-group">
                        <button name="freischaltensubmit" type="submit" value="Markierte freischalten" class="btn btn-primary"><i class="fa fa-thumbs-up"></i> Markierte freischalten</button>
                        <button name="freischaltenleoschen" type="submit" value="Markierte l&ouml;schen" class="btn btn-danger"><i class="fa fa-trash"></i> Markierte l&ouml;schen</button>
                    </div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>

        <div id="newsletter" class="tab-pane fade {if isset($cTab) && $cTab === 'newsletter'} active in{/if}">
            {if $oNewsletterEmpfaenger_arr|@count > 0 && $oNewsletterEmpfaenger_arr}
                {include file='pagination.tpl' cSite=5 cUrl='freischalten.php' oBlaetterNavi=$oBlaetterNaviNewsKommentar cParams='' hash='#newsletter'}
                <form method="post" action="freischalten.php">
                    {$jtl_token}
                    <input type="hidden" name="freischalten" value="1" />
                    <input type="hidden" name="newsletterempfaenger" value="1" />
                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="check">&nbsp;</th>
                            <th class="tleft">{#freischaltenNewsletterReceiverEmail#}</th>
                            <th class="tleft">{#freischaltenNewsletterReceiverFirstName#}</th>
                            <th class="tleft">{#freischaltenNewsletterReceiverLastName#}</th>
                            <th>(<a href="freischalten.php?{$cSuchStr}nSort=4{if !isset($nSort) || $nSort != 44}4{/if}{if $oBlaetterNaviNewsletterEmpfaenger->nAktuelleSeite > 0}&s1={$oBlaetterNaviNewsletterEmpfaenger->nAktuelleSeite}{/if}&token={$smarty.session.jtl_token}">{if !isset($nSort) || $nSort != 44}Alt...Neu{elseif isset($nSort) && $nSort == 44}Neu...Alt{/if}</a>) {#freischaltenNewsletterReceiverDate#}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach name=newsletterempfaenger from=$oNewsletterEmpfaenger_arr item=oNewsletterEmpfaenger}
                            <tr>
                                <td class="check"><input type="checkbox" name="kNewsletterEmpfaenger[]" value="{$oNewsletterEmpfaenger->kNewsletterEmpfaenger}" /></td>
                                <td>{$oNewsletterEmpfaenger->cEmail}</td>
                                <td>{$oNewsletterEmpfaenger->cVorname}</td>
                                <td>{$oNewsletterEmpfaenger->cNachname}</td>
                                <td class="tcenter">{$oNewsletterEmpfaenger->dEingetragen_de}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                        <tfoot>
                        <tr>
                            <td class="check"><input name="ALLMSGS" id="ALLMSGS5" type="checkbox" onclick="AllMessages(this.form);" /></td>
                            <td colspan="5"><label for="ALLMSGS5">{#freischaltenSelectAll#}</label></td>
                        </tr>
                        </tfoot>
                    </table>
                    <div class="save_wrapper btn-group">
                        <button name="freischaltensubmit" type="submit" value="Markierte freischalten" class="btn btn-primary"><i class="fa fa-thumbs-up"></i> Markierte freischalten</button>
                        <button name="freischaltenleoschen" type="submit" value="Markierte l&ouml;schen" class="btn btn-danger"><i class="fa fa-trash"></i> Markierte l&ouml;schen</button>
                    </div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}