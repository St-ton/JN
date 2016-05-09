{if
    $Artikel->cBeschreibung|strlen > 0 ||
    $Einstellungen.artikeldetails.artikeldetails_fragezumprodukt_anzeigen === 'Y' ||
    ($Einstellungen.artikeldetails.merkmale_anzeigen === 'Y' && $Artikel->oMerkmale_arr|count > 1) ||
    $Einstellungen.bewertung.bewertung_anzeigen === 'Y' ||
    ($Einstellungen.preisverlauf.preisverlauf_anzeigen === 'Y' && $bPreisverlauf) ||
    $verfuegbarkeitsBenachrichtigung == 1 ||
    ((($Einstellungen.artikeldetails.mediendatei_anzeigen === 'YM' && $Artikel->cMedienDateiAnzeige !== 'beschreibung') || $Artikel->cMedienDateiAnzeige === 'tab') && $Artikel->cMedienTyp_arr|@count > 0 && $Artikel->cMedienTyp_arr)}
    {if $Einstellungen.artikeldetails.artikeldetails_tabs_nutzen !== 'N'}
        {assign var=tabanzeige value=true}
    {else}
        {assign var=tabanzeige value=false}
    {/if}

    <div id="article-tabs" {if $tabanzeige}class="tab-content"{/if}>
        {* ARTIKELBESCHREIBUNG *}
        {if $Artikel->cBeschreibung|strlen > 0 || $Einstellungen.artikeldetails.merkmale_anzeigen === 'Y' && $Artikel->oMerkmale_arr|count > 1}
            <div role="tabpanel" class="{if $tabanzeige}tab-pane{else}panel panel-default{/if}" id="tab-description">
                <div class="panel-heading" {if $tabanzeige}data-toggle="collapse" {/if}data-parent="#article-tabs" data-target="#tab-description">
                    <h3 class="panel-title">{lang key="description" section="productDetails"}</h3>
                </div>
                {assign var=cArtikelBeschreibung value=$Artikel->cBeschreibung}
                <div class="panel-body">
                    <div class="tab-content-wrapper">
                        <div class="desc">
                            {$cArtikelBeschreibung}

                            {if ($Einstellungen.artikeldetails.mediendatei_anzeigen === 'YA' && $Artikel->cMedienDateiAnzeige !== 'tab') || $Artikel->cMedienDateiAnzeige === 'beschreibung'}
                                {if !empty($Artikel->cMedienTyp_arr)}
                                    {foreach name="mediendateigruppen" from=$Artikel->cMedienTyp_arr item=cMedienTyp}
                                        <div class="media">
                                            {include file='productdetails/mediafile.tpl'}
                                        </div>
                                    {/foreach}
                                {/if}
                            {/if}
                        </div>
                        {include file="productdetails/attributes.tpl" tplscope="details"}
                    </div>
                </div>
            </div>
        {/if}
        {section name=iterator start=1 loop=10}
            {assign var=tab value=tab}
            {assign var=tabname value=$tab|cat:$smarty.section.iterator.index|cat:" name"}
            {assign var=tabinhalt value=$tab|cat:$smarty.section.iterator.index|cat:" inhalt"}
            {if isset($tab1)}{$tab1}{/if}
            {if isset($Artikel->AttributeAssoc[$tabname]) && $Artikel->AttributeAssoc[$tabname] && $Artikel->AttributeAssoc[$tabinhalt]}
                <div role="tabpanel" class="{if $tabanzeige}tab-pane{else}panel panel-default{/if}" id="{$tabname|replace:' ':'-'}">
                    <div class="panel-heading" {if $tabanzeige}data-toggle="collapse" {/if}data-parent="#article-tabs" data-target="#{$tabname|replace:' ':'-'}">
                        <h3 class="panel-title">{$Artikel->AttributeAssoc[$tabname]}</h3>
                    </div>
                    <div class="panel-body">
                        <div class="tab-content-wrapper">
                            {$Artikel->AttributeAssoc[$tabinhalt]}
                        </div>
                    </div>
                </div>
            {/if}
        {/section}
        {* BEWERTUNGEN *}
        {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y'}
            <div role="tabpanel" class="{if $tabanzeige}tab-pane{else}panel panel-default{/if}" id="tab-votes">
                <div class="panel-heading" {if $tabanzeige}data-toggle="collapse" {/if}data-parent="#article-tabs" data-target="#tab-votes">
                    <h3 class="panel-title">{lang key="Votes" section="global"} ({$Artikel->Bewertungen->oBewertungGesamt->nAnzahl})</h3>
                </div>
                <div class="tab-content-wrapper">
                    <div class="panel-body">
                        {include file='productdetails/reviews.tpl' stars=$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt}
                    </div>
                </div>
            </div>
        {/if}
        {* FRAGE ZUM PRODUKT *}
        {if $Einstellungen.artikeldetails.artikeldetails_fragezumprodukt_anzeigen === 'Y'}
            <div role="tabpanel" class="{if $tabanzeige}tab-pane{else}panel panel-default{/if}" id="tab-productquestion">
                <div class="panel-heading" {if $tabanzeige}data-toggle="collapse" {/if}data-parent="#article-tabs" data-target="#tab-productquestion">
                    <h3 class="panel-title">{lang key="productQuestion" section="productDetails"}</h3>
                </div>
                <div class="tab-content-wrapper">
                    <div class="panel-body">
                        {include file='productdetails/question_on_item.tpl'}
                    </div>
                </div>
            </div>
        {/if}
        {* PREISVERLAUF *}
        {if $Einstellungen.preisverlauf.preisverlauf_anzeigen === 'Y' && $bPreisverlauf}
            <div role="tabpanel" class="{if $tabanzeige}tab-pane{else}panel panel-default{/if}" id="tab-preisverlauf">
                <div class="panel-heading" {if $tabanzeige}data-toggle="collapse" {/if}data-parent="#article-tabs" data-target="#tab-preisverlauf">
                    <h3 class="panel-title">{lang key="priceFlow" section="productDetails"}</h3>
                </div>
                <div class="tab-content-wrapper">
                    <div class="panel-body">
                        {include file='productdetails/price_history.tpl'}
                    </div>
                </div>
            </div>
        {/if}
        {* VERFUEGBARKEITSBENACHRICHTIGUNG *}
        {if $verfuegbarkeitsBenachrichtigung == 1 && $Artikel->cLagerBeachten === 'Y'}
            <div role="tabpanel" class="{if $tabanzeige}tab-pane{else}panel panel-default{/if}" id="tab-benachrichtigung">
                <div class="panel-heading" {if $tabanzeige}data-toggle="collapse" {/if}data-parent="#article-tabs" data-target="#tab-benachrichtigung">
                    <h3 class="panel-title">{lang key="notifyMeWhenProductAvailableAgain" section="global"}</h3>
                </div>
                <div class="tab-content-wrapper">
                    <div class="panel-body">
                        {include file='productdetails/availability_notification_form.tpl' tplscope='artikeldetails'}
                    </div>
                </div>
            </div>
        {/if}
        {* MEDIENDATEIEN *}
        {if ($Einstellungen.artikeldetails.mediendatei_anzeigen === 'YM' && $Artikel->cMedienDateiAnzeige !== 'beschreibung') || $Artikel->cMedienDateiAnzeige === 'tab'}
            {if !empty($Artikel->cMedienTyp_arr)}
                {foreach name="mediendateigruppen" from=$Artikel->cMedienTyp_arr item=cMedienTyp}
                    {$cMedienTypId = $cMedienTyp|regex_replace:"/[\'\" ]/":""}
                    <div role="tabpanel" class="{if $tabanzeige}tab-pane{else}panel panel-default{/if}" id="tab-{$cMedienTypId}">
                        <div class="panel-heading" {if $tabanzeige}data-toggle="collapse" {/if}data-parent="#article-tabs" data-target="#tab-{$cMedienTypId}">
                            <h3 class="panel-title">{$cMedienTyp}</h3>
                        </div>
                        <div class="tab-content-wrapper">
                            <div class="panel-body">
                                {include file='productdetails/mediafile.tpl'}
                            </div>
                        </div>
                    </div>
                {/foreach}
            {/if}
        {/if}
        {* TAGS *}
        {if $Einstellungen.artikeldetails.tagging_anzeigen === 'Y' && (count($ProduktTagging) > 0 || $Einstellungen.artikeldetails.tagging_freischaltung !== 'N')}
            <div role="tabpanel" class="{if $tabanzeige}tab-pane{else}panel panel-default{/if}" id="tab-tags">
                <div class="panel-heading" {if $tabanzeige}data-toggle="collapse" {/if}data-parent="#article-tabs" data-target="#tab-tags">
                    <h3 class="panel-title">{lang key="productTags" section="productDetails"}</h3>
                </div>
                <div class="tab-content-wrapper">
                    <div class="panel-body">
                        {include file='productdetails/tags.tpl'}
                    </div>
                </div>
            </div>
        {/if}
    </div>{* /article-tabs *}
    <hr>
{/if}