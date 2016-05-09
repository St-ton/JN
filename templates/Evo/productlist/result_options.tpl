{assign var='show_filters' value=false}
{if $Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab == 0 || count($Suchergebnisse->Artikel->elemente) >= $Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab || $NaviFilter->nAnzahlFilter > 0}
    {assign var='show_filters' value=true}
{/if}
<div id="result-options" class="well panel-wrap{if !$show_filters} hidden-xs{/if}">
    <div class="row">
        <div class="col-sm-8 col-sm-push-4 displayoptions form-inline text-right hidden-xs">
            {block name="productlist-result-options-sort"}
            <div class="form-group">
                <select name="Sortierung" onchange="$('#improve_search').submit();" class="form-control form-small">
                    {if !isset($Suchergebnisse->Sortierung) || !$Suchergebnisse->Sortierung}
                        <option value="0">{lang key="sorting" section="productOverview"}</option>{/if}
                    <option value="100" {if isset($smarty.session.Usersortierung) && isset($Sort) && $smarty.session.Usersortierung==$Sort->value}selected="selected"{/if}>{lang key="standard" section="global"}</option>
                    {foreach name=sortierliste from=$Sortierliste item=Sort}
                        <option value="{$Sort->value}" {if $smarty.session.Usersortierung==$Sort->value}selected="selected"{/if}>{$Sort->angezeigterName}</option>
                    {/foreach}
                </select>
            </div>
            <div class="form-group">
                <select name="af" onchange="$('#improve_search').submit();" class="form-control form-small">
                    <option value="0"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 0} selected="selected"{/if}>{lang key="productsPerPage" section="productOverview"}</option>
                    <option value="9"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 9} selected="selected"{/if}>9 {lang key="productsPerPage" section="productOverview"}</option>
                    <option value="18"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 18} selected="selected"{/if}>18 {lang key="productsPerPage" section="productOverview"}</option>
                    <option value="30"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 30} selected="selected"{/if}>30 {lang key="productsPerPage" section="productOverview"}</option>
                    <option value="90"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 90} selected="selected"{/if}>90 {lang key="productsPerPage" section="productOverview"}</option>
                </select>
            </div>
            {if isset($oErweiterteDarstellung) && isset($Einstellungen.artikeluebersicht.artikeluebersicht_erw_darstellung) && $Einstellungen.artikeluebersicht.artikeluebersicht_erw_darstellung === 'Y'}
                <div class="btn-group">
                    <a href="{$oErweiterteDarstellung->cURL_arr[1]}" id="ed_list" class="btn btn-default btn-option ed list {if $oErweiterteDarstellung->nDarstellung == 1}active{/if}" role="button" title="{lang key="list" section="productOverview"}"><span class="fa fa-th-list"></span></a>
                    <a href="{$oErweiterteDarstellung->cURL_arr[2]}" id="ed_gallery" class="btn btn-default btn-option ed gallery {if $oErweiterteDarstellung->nDarstellung == 2}active{/if}" role="button" title="{lang key="gallery" section="productOverview"}"><span class="fa fa-th-large"></span></a>
                </div>
            {/if}
            {/block}
        </div>
        {if $show_filters}
            <div class="col-sm-4 col-sm-pull-8 filter-collapsible-control">
                <a class="btn btn-default" data-toggle="collapse" href="#filter-collapsible" aria-expanded="false" aria-controls="filter-collapsible">
                    <span class="fa fa-filter"></span> {lang key='filterBy' section='global'}
                    <span class="caret"></span>
                </a>
            </div>
        {/if}
    </div>{* /row *}
    {if $show_filters}
        <div id="filter-collapsible" class="collapse top10">
        <nav class="panel panel-default">
            <div id="navbar-filter" class="panel-body">
                <div class="form-inline">
                    {if $Einstellungen.navigationsfilter.allgemein_kategoriefilter_benutzen === 'Y' && ((!empty($Suchergebnisse->Kategorieauswahl) && $Suchergebnisse->Kategorieauswahl|@count > 1) || isset($NaviFilter->KategorieFilter->kKategorie->kKategorie) && $NaviFilter->KategorieFilter->kKategorie > 0)}
                        {block name="productlist-result-options-filter-category"}
                        <div class="form-group">
                            <select name="kf" onchange="$('#improve_search').submit();" class="form-control form-small">
                                {if !empty($NaviFilter->KategorieFilter->kKategorie) && $NaviFilter->KategorieFilter->kKategorie > 0 || $Einstellungen.navigationsfilter.kategoriefilter_anzeigen_als === 'HF' || (empty($NaviFilter->Kategorie->kKategorie) && empty($NaviFilter->KategorieFilter->kKategorie))}
                                    <option value="0">{lang key="allCategories" section="productOverview"}</option>
                                {/if}
                                {if !empty($NaviFilter->Kategorie->kKategorie) || !empty($NaviFilter->KategorieFilter->kKategorie)}
                                    <option value="{if !empty($NaviFilter->KategorieFilter->kKategorie)}{$NaviFilter->KategorieFilter->kKategorie}{else}{$NaviFilter->Kategorie->kKategorie}{/if}" {if !empty($NaviFilter->KategorieFilter->kKategorie)}selected="selected"{/if}>
                                        {if $Einstellungen.navigationsfilter.kategoriefilter_anzeigen_als === 'HF' && !empty($NaviFilter->KategorieFilter->kKategorie)}
                                            {$NaviFilter->KategorieFilter->cName}
                                        {else}
                                            {$Suchergebnisse->Kategorieauswahl[0]->cName}
                                        {/if}
                                    </option>
                                {/if}
                                {if empty($NaviFilter->Kategorie->kKategorie) && (empty($NaviFilter->KategorieFilter->kKategorie) || $Einstellungen.navigationsfilter.kategoriefilter_anzeigen_als === 'HF')}
                                    {foreach name=kategorieauswahl from=$Suchergebnisse->Kategorieauswahl item=Kategorie}
                                        {if (isset($Kategorie->kKategorie) && (empty($NaviFilter->KategorieFilter->kKategorie) || ($Kategorie->kKategorie != $NaviFilter->KategorieFilter->kKategorie)))}
                                            <option value="{$Kategorie->kKategorie}">{$Kategorie->cName} {if !isset($nMaxAnzahlArtikel) || !$nMaxAnzahlArtikel}({$Kategorie->nAnzahl}){/if}</option>
                                        {/if}
                                    {/foreach}
                                {/if}
                            </select>
                        </div>
                        {/block}
                    {/if}

                    {if $Einstellungen.navigationsfilter.allgemein_herstellerfilter_benutzen === 'Y' && !isset($oExtendedJTLSearchResponse) && (!empty($Suchergebnisse->Herstellerauswahl) || !empty($NaviFilter->HerstellerFilter))}
                        {block name="productlist-result-options-filter-manufacturer"}
                        <div class="form-group">
                            <select id="hf" name="hf" class="form-control form-small suche_improve_search" onchange="$('#improve_search').submit();">
                                {if (isset($NaviFilter->Hersteller->kHersteller) && $NaviFilter->Hersteller->kHersteller > 0) || (isset($NaviFilter->HerstellerFilter->kHersteller) && $NaviFilter->HerstellerFilter->kHersteller > 0)}
                                    {if !empty($NaviFilter->HerstellerFilter->kHersteller)}
                                        <option value="0">{lang key="allManufacturers" section="global"}</option>
                                    {/if}
                                    {if !empty($NaviFilter->Hersteller->kHersteller)}
                                        <option value="{$NaviFilter->Hersteller->kHersteller}" selected="selected">{$NaviFilter->Hersteller->cName}</option>
                                    {/if}
                                {else}
                                    <option value="0">{lang key="allManufacturers" section="global"}</option>
                                    {foreach name=herstellerauswahl from=$Suchergebnisse->Herstellerauswahl item=Hersteller}
                                        <option value="{$Hersteller->kHersteller}">{$Hersteller->cName} {if !isset($nMaxAnzahlArtikel) || !$nMaxAnzahlArtikel}({$Hersteller->nAnzahl}){/if}</option>
                                    {/foreach}
                                {/if}
                            </select>
                        </div>
                        {/block}
                    {/if}

                    {if $Einstellungen.navigationsfilter.merkmalfilter_verwenden === 'content' && $Suchergebnisse->MerkmalFilter|@count > 0 && $Suchergebnisse->Artikel->elemente|@count > 0}
                        {block name="productlist-result-options-filter-attributes"}
                        {foreach name=merkmalfilter from=$Suchergebnisse->MerkmalFilter item=Merkmal}
                            <div class="form-group dropdown">
                                <a href="#" class="btn btn-default dropdown-toggle form-control" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {$Merkmal->cName} <span class="caret"></span>
                                </a>
                                {include file='snippets/filter/characteristic.tpl' class="dropdown-menu" role="menu"}
                            </div>
                        {/foreach}
                        {/block}
                    {/if}{* /merkmalfilter *}
                    {if isset($Suchergebnisse->Suchspecialauswahl) && (
                    (isset($Suchergebnisse->Suchspecialauswahl[1]->nAnzahl) && $Suchergebnisse->Suchspecialauswahl[1]->nAnzahl > 0) ||
                    (isset($Suchergebnisse->Suchspecialauswahl[2]->nAnzahl) && $Suchergebnisse->Suchspecialauswahl[2]->nAnzahl > 0) ||
                    (isset($Suchergebnisse->Suchspecialauswahl[3]->nAnzahl) && $Suchergebnisse->Suchspecialauswahl[3]->nAnzahl > 0) ||
                    (isset($Suchergebnisse->Suchspecialauswahl[4]->nAnzahl) && $Suchergebnisse->Suchspecialauswahl[4]->nAnzahl > 0) ||
                    (isset($Suchergebnisse->Suchspecialauswahl[5]->nAnzahl) && $Suchergebnisse->Suchspecialauswahl[5]->nAnzahl > 0) ||
                    (isset($Suchergebnisse->Suchspecialauswahl[6]->nAnzahl) && $Suchergebnisse->Suchspecialauswahl[6]->nAnzahl > 0))}
                        <div class="form-group dropdown">
                            <a href="#" class="btn btn-default dropdown-toggle form-control" data-toggle="dropdown" role="button" aria-expanded="false">
                                {lang key="specificProducts" section="global"} <span class="caret"></span>
                            </a>
                            {include file='snippets/filter/special.tpl' class="dropdown-menu"}
                        </div>
                    {/if}{* /suchspecials *}
                    {if $Einstellungen.navigationsfilter.preisspannenfilter_benutzen === 'content' && (isset($NaviFilter->PreisspannenFilter) || !empty($Suchergebnisse->Preisspanne))}
                        {block name="productlist-result-options-filter-price"}
                        <div class="form-group dropdown">
                            <a href="#" class="btn btn-default dropdown-toggle form-control" data-toggle="dropdown" role="button" aria-expanded="false">
                                {lang key="rangeOfPrices" section="global"} <span class="caret"></span>
                            </a>
                            {include file='snippets/filter/pricerange.tpl' class="dropdown-menu"}
                        </div>
                        {/block}
                    {elseif isset($NaviFilter->PreisspannenFilter) && $NaviFilter->PreisspannenFilter->fBis > 0}
                        <input type="hidden" name="pf" value="{$NaviFilter->PreisspannenFilter->cWert}">
                    {/if}{* /preisspannenfilter *}

                    {if $Einstellungen.navigationsfilter.bewertungsfilter_benutzen === 'content' && !empty($Suchergebnisse->Bewertung)}
                        {block name="productlist-result-options-filter-rating"}
                        <div class="form-group dropdown">
                            <a href="#" class="btn btn-default dropdown-toggle form-control" data-toggle="dropdown" role="button" aria-expanded="false">
                                {lang key="Votes" section="global"} <span class="caret"></span>
                            </a>
                            {include file='snippets/filter/review.tpl' class="dropdown-menu"}
                        </div>
                        {/block}
                    {elseif isset($NaviFilter->BewertungFilter) && $NaviFilter->BewertungFilter->nSterne > 0}
                        <input type="hidden" name="bf" value="{$NaviFilter->BewertungFilter->nSterne}">
                    {/if}
                </div>{* /form-inline *}
            </div>
            <!-- /.navbar-collapse -->
        </nav>
        </div>{* /collapse *}
        {if $NaviFilter->nAnzahlFilter > 0}
            <div class="clearfix top10"></div>
            <div class="active-filters panel panel-default">
            <div class="panel-body">
                {if $NaviFilter->SuchspecialFilter->kKey > 0 && (!isset($NaviFilter->Suchspecial) || $NaviFilter->Suchspecial->kKey != $NaviFilter->SuchspecialFilter->kKey)}
                    {strip}
                    <a rel="nofollow" title="{lang key="specificProducts" section="global"}" href="{$NaviFilter->URL->cAlleSuchspecials}" class="label label-info">
                        {if $NaviFilter->SuchspecialFilter->kKey == 1}
                            {lang key="bestsellers" section="global"}
                        {elseif $NaviFilter->SuchspecialFilter->kKey == 2}
                            {lang key="specialOffer" section="global"}
                        {elseif $NaviFilter->SuchspecialFilter->kKey == 3}
                            {lang key="newProducts" section="global"}
                        {elseif $NaviFilter->SuchspecialFilter->kKey == 4}
                            {lang key="topOffer" section="global"}
                        {elseif $NaviFilter->SuchspecialFilter->kKey == 5}
                            {lang key="upcomingProducts" section="global"}
                        {elseif $NaviFilter->SuchspecialFilter->kKey == 6}
                            {lang key="topReviews" section="global"}
                        {/if}
                        &nbsp;
                        <span class="fa fa-trash-o"></span>
                    </a>
                    {/strip}
                {/if}
                {if !empty($NaviFilter->KategorieFilter->kKategorie)}
                    {strip}
                        <a href="{$NaviFilter->URL->cAlleKategorien}" class="label label-info">{if $Einstellungen.navigationsfilter.kategoriefilter_anzeigen_als === 'HF' && !empty($NaviFilter->KategorieFilter->kKategorie) && $NaviFilter->KategorieFilter->kKategorie > 0}{$NaviFilter->KategorieFilter->cName}{else}{$Suchergebnisse->Kategorieauswahl[0]->cName}{/if}
                            &nbsp;<span class="fa fa-trash-o"></span>
                        </a>
                    {/strip}
                {/if}
                {if !empty($NaviFilter->Hersteller->kHersteller) || !empty($NaviFilter->HerstellerFilter->kHersteller)}
                    {strip}
                        <a href="{$NaviFilter->URL->cAlleHersteller}" class="label label-info">{$Suchergebnisse->Herstellerauswahl[0]->cName}
                            &nbsp;<span class="fa fa-trash-o"></span>
                        </a>
                    {/strip}
                {/if}
                {if !empty($NaviFilter->PreisspannenFilter->fBis)}
                    {strip}
                        <a href="{$NaviFilter->URL->cAllePreisspannen}" class="label label-info">{$NaviFilter->PreisspannenFilter->cVonLocalized}
                        - {$NaviFilter->PreisspannenFilter->cBisLocalized}
                        &nbsp;<span class="fa fa-trash-o"></span>
                        </a>{/strip}
                {/if}
                {if !empty($NaviFilter->BewertungFilter->nSterne)}
                    {strip}
                        <a href="{$NaviFilter->URL->cAlleBewertungen}" class="label label-info">{lang key="from" section="productDetails"} {$NaviFilter->BewertungFilter->nSterne} {if $NaviFilter->BewertungFilter->nSterne > 1}{lang key="starPlural"}{else}{lang key="starSingular"}{/if}
                            &nbsp;<span class="fa fa-trash-o"></span>
                        </a>
                    {/strip}
                {/if}
                {foreach name=merkmalfilter from=$Suchergebnisse->MerkmalFilter item=Merkmal}
                    {foreach name=merkmalwertfilter from=$Merkmal->oMerkmalWerte_arr item=MerkmalWert}
                        {if $MerkmalWert->nAktiv}
                            {assign var=kMerkmalWert value=$MerkmalWert->kMerkmalWert}
                            {strip}
                                <a class="label label-info" rel="nofollow" href="{if !empty($MerkmalWert->cURL)}{$MerkmalWert->cURL}{else}#{/if}">
                                    {*<a class="label label-info" rel="nofollow" href="{$NaviFilter->URL->cAlleMerkmalWerte[$kMerkmalWert]}">*}
                                    <i class="fa fa-check-circle-o"></i> {$MerkmalWert->cWert} &nbsp;<span class="fa fa-trash-o"></span>
                                </a>
                            {/strip}
                        {/if}
                    {/foreach}
                {/foreach}
                {if !empty($NaviFilter->URL->cNoFilter)}
                    {strip}
                        <a href="{$NaviFilter->URL->cNoFilter}" class="label label-warning">
                            {lang key="removeFilters" section="global"}
                        </a>
                    {/strip}
                {/if}
            </div>
            </div>{* /active-filters *}
        {/if}
    {/if}
</div>