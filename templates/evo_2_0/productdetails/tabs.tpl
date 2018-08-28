{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{$tabanzeige = $Einstellungen.artikeldetails.artikeldetails_tabs_nutzen !== 'N'}
{$showProductWeight = false}
{$showShippingWeight = false}
{if isset($Artikel->cArtikelgewicht) && $Artikel->fArtikelgewicht > 0
&& $Einstellungen.artikeldetails.artikeldetails_artikelgewicht_anzeigen === 'Y'}
    {$showProductWeight = true}
{/if}
{if isset($Artikel->cGewicht) && $Artikel->fGewicht > 0
&& $Einstellungen.artikeldetails.artikeldetails_gewicht_anzeigen === 'Y'}
    {$showShippingWeight = true}
{/if}
{$dimension = $Artikel->getDimension()}
{$showAttributesTable = ($Einstellungen.artikeldetails.merkmale_anzeigen === 'Y'
&& !empty($Artikel->oMerkmale_arr) || $showProductWeight || $showShippingWeight
|| $Einstellungen.artikeldetails.artikeldetails_abmessungen_anzeigen === 'Y'
&& (!empty($dimension['length']) || !empty($dimension['width']) || !empty($dimension['height']))
|| isset($Artikel->cMasseinheitName) && isset($Artikel->fMassMenge) && $Artikel->fMassMenge > 0
&& $Artikel->cTeilbar !== 'Y' && ($Artikel->fAbnahmeintervall == 0 || $Artikel->fAbnahmeintervall == 1)
|| ($Einstellungen.artikeldetails.artikeldetails_attribute_anhaengen === 'Y'
|| (isset($Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ATTRIBUTEANHAENGEN])
&& $Artikel->FunktionsAttribute[$FKT_ATTRIBUT_ATTRIBUTEANHAENGEN] == 1)) && !empty($Artikel->Attribute))}
{$useDescriptionWithMediaGroup = ((($Einstellungen.artikeldetails.mediendatei_anzeigen === 'YA'
&& $Artikel->cMedienDateiAnzeige !== 'tab') || $Artikel->cMedienDateiAnzeige === 'beschreibung')
&& !empty($Artikel->cMedienTyp_arr))}
{$useDescription = (($Artikel->cBeschreibung|strlen > 0) || $useDescriptionWithMediaGroup || $showAttributesTable)}
{$useDownloads = (isset($Artikel->oDownload_arr) && $Artikel->oDownload_arr|@count > 0)}
{$useVotes = $Einstellungen.bewertung.bewertung_anzeigen === 'Y'}
{$useQuestionOnItem = $Einstellungen.artikeldetails.artikeldetails_fragezumprodukt_anzeigen === 'Y'}
{$usePriceFlow = ($Einstellungen.preisverlauf.preisverlauf_anzeigen === 'Y' && $bPreisverlauf)}
{$useAvailabilityNotification = ($verfuegbarkeitsBenachrichtigung == 1 && $Artikel->cLagerBeachten === 'Y')}
{$useMediaGroup = ((($Einstellungen.artikeldetails.mediendatei_anzeigen === 'YM'
&& $Artikel->cMedienDateiAnzeige !== 'beschreibung') || $Artikel->cMedienDateiAnzeige === 'tab')
&& !empty($Artikel->cMedienTyp_arr))}
{$useTags = ($Einstellungen.artikeldetails.tagging_anzeigen === 'Y' && (count($ProduktTagging) > 0
|| $Einstellungen.artikeldetails.tagging_freischaltung !== 'N'))}
{$hasVotesHash = isset($smarty.get.ratings_nPage)
|| isset($smarty.get.bewertung_anzeigen)
|| isset($smarty.get.ratings_nItemsPerPage)
|| isset($smarty.get.ratings_nSortByDir)
|| isset($smarty.get.btgsterne)}
{section name=iterator start=1 loop=10}
    {$tab = tab}
    {$tabname = $tab|cat:$smarty.section.iterator.index|cat:" name"}
    {$tabinhalt = $tab|cat:$smarty.section.iterator.index|cat:" inhalt"}
    {if isset($Artikel->AttributeAssoc[$tabname]) && $Artikel->AttributeAssoc[$tabname]
    && $Artikel->AttributeAssoc[$tabinhalt]}
        {$separatedTabs[{$tabname|replace:' ':'-'}] = [
        'id'      => {$tabname|replace:' ':'-'},
        'name'   => {$Artikel->AttributeAssoc[$tabname]},
        'content' => {$Artikel->AttributeAssoc[$tabinhalt]}
        ]}
    {/if}
{/section}
{$setActiveClass = [
'description'    => (!$hasVotesHash),
'downloads'      => (!$hasVotesHash && !$useDescription),
'separatedTabs'  => (!$hasVotesHash && !$useDescription && !$useDownloads),
'votes'          => ($hasVotesHash || !$useDescription && !$useDownloads && empty($separatedTabs)),
'questionOnItem' => (!$hasVotesHash && !$useDescription && !$useDownloads && empty($separatedTabs) && !$useVotes),
'priceFlow'      => (!$useVotes && !$hasVotesHash && !$useDescription && !$useDownloads && empty($separatedTabs)
&& !$useQuestionOnItem),
'availabilityNotification' => (!$useVotes && !$hasVotesHash && !$useDescription && !$useDownloads
&& empty($separatedTabs) && !$useQuestionOnItem && !$usePriceFlow),
'mediaGroup' => (!$useVotes && !$hasVotesHash && !$useDescription && !$useDownloads && empty($separatedTabs)
&& !$useQuestionOnItem && !$usePriceFlow && !$useAvailabilityNotification),
'tags' => (!$useVotes && !$hasVotesHash && !$useDescription && !$useDownloads && empty($separatedTabs)
&& !$useQuestionOnItem && !$usePriceFlow && !$useAvailabilityNotification && !$useMediaGroup)
]}

{if useDescription || $useDownloads || $useDescriptionWithMediaGroup || $useVotes || $useQuestionOnItem || $usePriceFlow
|| $useAvailabilityNotification || $useMediaGroup || $useTags || !empty($separatedTabs)}
    {if $tabanzeige}
        <ul class="nav nav-tabs bottom15" role="tablist">
            {if $useDescription}
                <li role="presentation" {if $setActiveClass.description} class="active"{/if}>
                    <a href="#tab-description" aria-controls="tab-description" role="tab" data-toggle="tab">
                        {block name='tab-description-title'}{lang key='description' section='productDetails'}{/block}
                    </a>
                </li>
            {/if}
            {if $useDownloads}
                <li role="presentation" {if $setActiveClass.downloads} class="active"{/if}>
                    <a href="#tab-downloads" aria-controls="tab-downloads" role="tab" data-toggle="tab">
                        {lang section='productDownloads' key='downloadSection'}
                    </a>
                </li>
            {/if}
            {if !empty($separatedTabs)}
                {foreach $separatedTabs as $separatedTab}
                    <li role="presentation"
                            {if $setActiveClass.separatedTabs && $separatedTab@first}
                        class="active"
                            {/if}>
                        <a href="#tab-{$separatedTab.id}" aria-controls="tab-{$separatedTab.id}" role="tab" data-toggle="tab">
                            {$separatedTab.name}
                        </a>
                    </li>
                {/foreach}
            {/if}
            {if $useVotes}
                <li role="presentation" {if $setActiveClass.votes} class="active"{/if}>
                    <a href="#tab-votes" aria-controls="tab-votes" role="tab" data-toggle="tab">
                        {lang key='Votes' section='global'}
                    </a>
                </li>
            {/if}
            {if $useQuestionOnItem}
                <li role="presentation" {if $setActiveClass.questionOnItem} class="active" {/if}>
                    <a href="#tab-questionOnItem" aria-controls="tab-questionOnItem" role="tab" data-toggle="tab">
                        {lang key='productQuestion' section='productDetails'}
                    </a>
                </li>
            {/if}
            {if $usePriceFlow}
                <li role="presentation" {if $setActiveClass.priceFlow} class="active"{/if}>
                    <a href="#tab-priceFlow" aria-controls="tab-priceFlow" role="tab" data-toggle="tab">
                        {lang key='priceFlow' section='productDetails'}
                    </a>
                </li>
            {/if}
            {if $useAvailabilityNotification}
                <li role="presentation"
                        {if $setActiveClass.availabilityNotification} class="active"{/if}>
                    <a href="#tab-availabilityNotification" aria-controls="tab-availabilityNotification" role="tab" data-toggle="tab">
                        {lang key='notifyMeWhenProductAvailableAgain' section='global'}
                    </a>
                </li>
            {/if}
            {if $useMediaGroup}
                {foreach $Artikel->cMedienTyp_arr as $cMedienTyp}
                    {$cMedienTypId = $cMedienTyp|regex_replace:"/[\'\"\/ ]/":""}
                    <li role="presentation"
                            {if $setActiveClass.mediaGroup && $cMedienTyp@first} class="active"{/if}>
                        <a href="#tab-{$cMedienTypId}" aria-controls="tab-{$cMedienTypId}" role="tab" data-toggle="tab">
                            {$cMedienTyp}
                        </a>
                    </li>
                {/foreach}
            {/if}
            {if $useTags}
                <li role="presentation" {if $setActiveClass.tags} class="active"{/if}>
                    <a href="#tab-tags" aria-controls="tab-tags" role="tab" data-toggle="tab">
                        {lang key='productTags' section='productDetails'}
                    </a>
                </li>
            {/if}
        </ul>
    {/if}
    <div class="tab-content" id="article-tabs">
        {if !$tabanzeige}
            <div class="panel-group" role="tablist" aria-multiselectable="false">
        {/if}
        {if $useDescription}
            {if $tabanzeige}
                <div role="tabpanel" class="tab-pane fade {if $setActiveClass.description} in active{/if}" id="tab-description">
            {else}
                <div class="panel panel-default" role="tab">
                    <div class="panel-heading">
                        <a role="button" data-toggle="collapse" href="#tab-description" aria-expanded="true">
                            <h3 class="panel-title">
                                {block name='tab-description-title'}{lang key='description' section='productDetails'}
                                    <i class="fa fa-chevron-down pull-right"></i>
                                {/block}
                            </h3>
                        </a>
                    </div>
                    <div  id="tab-description" class="panel-collapse collapse in" role="tabpanel">
                        <div class="panel-body">
            {/if}
            <div class="tab-content-wrapper">
                {block name='tab-description'}
                    {block name='tab-description-content'}
                        <div class="desc{if (!empty($Artikel->cBeschreibung) || $useDescriptionWithMediaGroup) && $showAttributesTable} bottom17{/if}">
                            {if $Artikel->cBeschreibung|strlen > 0}
                            <div class="bottom17">{$Artikel->cBeschreibung}</div>
                            {/if}
                            {if $useDescriptionWithMediaGroup}
                                {foreach $Artikel->cMedienTyp_arr as $cMedienTyp}
                                    <div class="media">
                                        {include file='productdetails/mediafile.tpl'}
                                    </div>
                                {/foreach}
                            {/if}
                        </div>
                    {/block}
                    {block name='tab-description-attributes'}
                        {include file='productdetails/attributes.tpl' tplscope='details'
                        showProductWeight=$showProductWeight showShippingWeight=$showShippingWeight
                        dimension=$dimension showAttributesTable=$showAttributesTable}
                    {/block}
                {/block}
            </div>
            {if $tabanzeige}
                </div>
            {else}
                        </div>
                    </div>
                </div>
            {/if}
        {/if}
        {if $useDownloads}
            {if $tabanzeige}
                <div role="tabpanel" class="tab-pane fade {if $setActiveClass.downloads} in active{/if}"
                id="tab-downloads">
            {else}
                <div class="panel panel-default" role="tab">
                    <div class="panel-heading">
                        <a role="button" data-toggle="collapse" href="#tab-downloads" aria-expanded="false">
                            <h3 class="panel-title">{lang section='productDownloads' key='downloadSection'}<i class="fa fa-chevron-down pull-right"></i></h3>
                        </a>
                    </div>
                    <div id="tab-downloads" class="panel-collapse collapse in" role="tabpanel">
                        <div class="panel-body">
            {/if}
            {include file='productdetails/download.tpl'}
            {if $tabanzeige}
                </div>
            {else}
                        </div>
                     </div>
                </div>
            {/if}
        {/if}
        {if !empty($separatedTabs)}
            {foreach $separatedTabs as $separatedTab}
                {if $tabanzeige}
                    <div role="tabpanel" class="tab-pane fade
                        {if $setActiveClass.separatedTabs && $separatedTab@first} in active{/if}"
                    id="tab-{$separatedTab.id}">
                {else}
                    <div class="panel panel-default" role="tab">
                    <div class="panel-heading">
                        <a role="button" data-toggle="collapse" href="#tab-{$separatedTab.id}" aria-expanded="false">
                            <h3 class="panel-title">{$separatedTab.name}<i class="fa fa-chevron-down pull-right"></i></h3>
                        </a>
                    </div>
                    <div id="tab-{$separatedTab.id}" class="panel-collapse collapse" role="tabpanel">
                        <div class="panel-body">
                {/if}
                {$separatedTab.content}
                {if $tabanzeige}
                    </div>
                {else}
                            </div>
                        </div>
                    </div>
                {/if}
            {/foreach}
        {/if}
        {if $useVotes}
            {if $tabanzeige}
                <div role="tabpanel" class="tab-pane fade {if $setActiveClass.votes} in active{/if}" id="tab-votes">
            {else}
                <div class="panel panel-default" role="tab">
                <div class="panel-heading">
                    <a role="button" data-toggle="collapse" href="#tab-votes" aria-expanded="false">
                        <h3 class="panel-title">{lang key='Votes'}<i class="fa fa-chevron-down pull-right"></i></h3>
                    </a>
                </div>
                <div id="tab-votes" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body">
            {/if}
            {include file='productdetails/reviews.tpl' stars=$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt}
            {if $tabanzeige}
                </div>
            {else}
                        </div>
                    </div>
                </div>
            {/if}
        {/if}
        {if $useQuestionOnItem}
            {if $tabanzeige}
                <div role="tabpanel" class="tab-pane fade {if $setActiveClass.questionOnItem} in active{/if}"
                id="tab-questionOnItem">
            {else}
                <div class="panel panel-default" role="tab">
                <div class="panel-heading">
                    <a role="button" data-toggle="collapse" href="#tab-questionOnItem" aria-expanded="false">
                        <h3 class="panel-title">{lang key='productQuestion' section='productDetails'}<i class="fa fa-chevron-down pull-right"></i></h3>
                    </a>
                </div>
                <div id="tab-questionOnItem" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body">
            {/if}
            {include file='productdetails/question_on_item.tpl' position="tab"}
            {if $tabanzeige}
                </div>
            {else}
                        </div>
                    </div>
                </div>
            {/if}
        {/if}
        {if $usePriceFlow}
            {if $tabanzeige}
                <div role="tabpanel" class="tab-pane fade {if $setActiveClass.priceFlow} in active{/if}"
                id="tab-priceFlow">
            {else}
                <div class="panel panel-default" role="tab">
                <div class="panel-heading">
                    <a role="button" data-toggle="collapse" href="#tab-priceFlow" aria-expanded="false">
                        <h3 class="panel-title">{lang key='priceFlow' section='productDetails'}<i class="fa fa-chevron-down pull-right"></i></h3>
                    </a>
                </div>
                <div id="tab-priceFlow" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body">
            {/if}
            {include file='productdetails/price_history.tpl'}
            {if $tabanzeige}
                </div>
            {else}
                        </div>
                    </div>
                </div>
            {/if}
        {/if}
        {if $useAvailabilityNotification}
            {if $tabanzeige}
                <div role="tabpanel" class="tab-pane fade {if $setActiveClass.availabilityNotification} in active{/if}"
                id="tab-availabilityNotification">
            {else}
                <div class="panel panel-default" role="tab">
                <div class="panel-heading">
                    <a role="button" data-toggle="collapse" href="#tab-availabilityNotification" aria-expanded="false">
                        <h3 class="panel-title">{lang key='notifyMeWhenProductAvailableAgain' section='global'}<i class="fa fa-chevron-down pull-right"></i></h3>
                    </a>
                </div>
                <div id="tab-availabilityNotification" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body">
            {/if}
            {include file='productdetails/availability_notification_form.tpl' position='tab' tplscope='artikeldetails'}
            {if $tabanzeige}
                </div>
            {else}
                        </div>
                    </div>
                </div>
            {/if}
        {/if}
        {if $useMediaGroup}
            {foreach $Artikel->cMedienTyp_arr as $cMedienTyp}
                {$cMedienTypId = $cMedienTyp|regex_replace:"/[\'\"\/ ]/":""}
                {if $tabanzeige}
                    <div role="tabpanel"
                    class="tab-pane fade{if $setActiveClass.mediaGroup && $cMedienTyp@first} in active{/if}"
                    id="tab-{$cMedienTypId}">
                {else}
                    <div class="panel panel-default" role="tab">
                    <div class="panel-heading">
                        <a role="button" data-toggle="collapse" href="#tab-{$cMedienTypId}" aria-expanded="false">
                            <h3 class="panel-title">{$cMedienTyp}<i class="fa fa-chevron-down pull-right"></i></h3>
                        </a>
                    </div>
                    <div id="tab-{$cMedienTypId}" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body">
                {/if}
                {include file='productdetails/mediafile.tpl'}
                {if $tabanzeige}
                    </div>
                {else}
                            </div>
                        </div>
                    </div>
                {/if}
            {/foreach}
        {/if}
        {if $useTags}
            {if $tabanzeige}
                <div role="tabpanel" class="tab-pane fade {if $setActiveClass.tags} in active{/if}" id="tab-tags">
            {else}
                <div class="panel panel-default" role="tab">
                <div class="panel-heading">
                    <a role="button" data-toggle="collapse" href="#tab-tags" aria-expanded="false">
                        <h3 class="panel-title">{lang key='productTags' section='productDetails'}<i class="fa fa-chevron-down pull-right"></i></h3>
                    </a>
                </div>
                <div id="tab-tags" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body" id="tab-tags">
            {/if}
            {include file='productdetails/tags.tpl'}
            {if $tabanzeige}
                </div>
            {else}
                        </div>
                    </div>
                </div>
            {/if}
        {/if}
    {if !$tabanzeige}
        </div>
    {/if}
    </div>
{/if}
