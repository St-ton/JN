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
{$useDescription = ($Artikel->cBeschreibung|strlen > 0 || $showAttributesTable)}
{$useDownloads = (isset($Artikel->oDownload_arr) && $Artikel->oDownload_arr|@count > 0)}
{$useMediaFiles = ((($Einstellungen.artikeldetails.mediendatei_anzeigen === 'YA'
    && $Artikel->cMedienDateiAnzeige !== 'tab') || $Artikel->cMedienDateiAnzeige === 'beschreibung')
    && !empty($Artikel->cMedienTyp_arr))}
{$useVotes = $Einstellungen.bewertung.bewertung_anzeigen === 'Y'}
{$useQuestionOnItem = $Einstellungen.artikeldetails.artikeldetails_fragezumprodukt_anzeigen === 'Y'}
{$usePriceFlow = ($Einstellungen.preisverlauf.preisverlauf_anzeigen === 'Y' && $bPreisverlauf)}
{$useAvailabilityNotification = ($verfuegbarkeitsBenachrichtigung == 1 && $Artikel->cLagerBeachten === 'Y')}
{$useMediaGroup = ((($Einstellungen.artikeldetails.mediendatei_anzeigen === 'YM'
    && $Artikel->cMedienDateiAnzeige !== 'beschreibung') || $Artikel->cMedienDateiAnzeige === 'tab')
    && !empty($Artikel->cMedienTyp_arr))}
{$useTags = ($Einstellungen.artikeldetails.tagging_anzeigen === 'Y' && (count($ProduktTagging) > 0
    || $Einstellungen.artikeldetails.tagging_freischaltung !== 'N'))}
{$setActive = isset($smarty.get.ratings_nPage) && count($smarty.get.ratings_nPage) > 0
    || isset($smarty.get.bewertung_anzeigen) && count($smarty.get.bewertung_anzeigen) > 0
    || isset($smarty.get.ratings_nItemsPerPage) && count($smarty.get.ratings_nItemsPerPage) > 0
    || isset($smarty.get.ratings_nSortByDir) && count($smarty.get.ratings_nSortByDir) > 0
    || isset($smarty.get.btgsterne) && count($smarty.get.btgsterne) > 0}
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

{if useDescription || $useDownloads || $useMediaFiles || $useVotes || $useQuestionOnItem || $usePriceFlow
    || $useAvailabilityNotification || $useMediaGroup || $useTags || !empty($separatedTabs)}
    {if $tabanzeige}
        <ul class="nav nav-tabs bottom15" role="tablist">
            {if $useDescription}
                <li role="presentation" {if !$setActive} class="active"{/if}>
                    <a href="#tab-description" aria-controls="tab-description" role="tab" data-toggle="tab">
                        {block name='tab-description-title'}{lang key='description' section='productDetails'}{/block}
                    </a>
                </li>
            {/if}
            {if $useDownloads}
                <li role="presentation" {if !$setActive && !$useDescription} class="active"{/if}>
                    <a href="#tab-downloads" aria-controls="tab-downloads" role="tab" data-toggle="tab">
                        {lang section="productDownloads" key="downloadSection"}
                    </a>
                </li>
            {/if}
            {if !empty($separatedTabs)}
                {foreach from=$separatedTabs item=separatedTab name="separatedTabsHeader"}
                    <li role="presentation"
                        {if !$setActive && !$useDescription && !$useDownloads
                            && $smarty.foreach.separatedTabsHeader.first}
                            class="active"
                        {/if}>
                        <a href="#tab-{$separatedTab.id}" aria-controls="tab-{$separatedTab.id}" role="tab" data-toggle="tab">
                            {$separatedTab.name}
                        </a>
                    </li>
                {/foreach}
            {/if}
            {if $useVotes}
                <li role="presentation"
                    {if $setActive}
                        class="active"
                    {/if}>
                    <a href="#tab-votes" aria-controls="tab-votes" role="tab" data-toggle="tab">
                        {lang key="Votes" section="global"}
                    </a>
                </li>
            {/if}
            {if $useQuestionOnItem}
                <li role="presentation"
                    {if !$setActive && !$useDescription && !$useDownloads && !empty($separatedTabs)
                        && !$useVotes} class="active"{/if}>
                    <a href="#tab-questionOnItem" aria-controls="tab-questionOnItem" role="tab" data-toggle="tab">
                        {lang key="productQuestion" section="productDetails"}
                    </a>
                </li>
            {/if}
            {if $usePriceFlow}
                <li role="presentation"
                    {if !$useVotes && !$setActive && !$useDescription && !$useDownloads && empty($separatedTabs)
                        && !$useQuestionOnItem} class="active"{/if}>
                    <a href="#tab-priceFlow" aria-controls="tab-priceFlow" role="tab" data-toggle="tab">
                        {lang key="priceFlow" section="productDetails"}
                    </a>
                </li>
            {/if}
            {if $useAvailabilityNotification}
                <li role="presentation"
                    {if !$useVotes && !$setActive && !$useDescription && !$useDownloads && empty($separatedTabs)
                        && !$useQuestionOnItem && !$usePriceFlow} class="active"{/if}>
                    <a href="#tab-availabilityNotification" aria-controls="tab-availabilityNotification" role="tab" data-toggle="tab">
                        {lang key="notifyMeWhenProductAvailableAgain" section="global"}
                    </a>
                </li>
            {/if}
            {if $useMediaGroup}
                {foreach name="mediendateigruppen" from=$Artikel->cMedienTyp_arr item=cMedienTyp}
                    {$cMedienTypId = $cMedienTyp|regex_replace:"/[\'\"\/ ]/":""}
                    <li role="presentation"
                        {if !$useVotes && !$setActive && !$useDescription && !$useDownloads && empty($separatedTabs)
                            && !$useQuestionOnItem && !$usePriceFlow && !$useAvailabilityNotification
                            && $smarty.foreach.mediendateigruppen.first} class="active"{/if}>
                        <a href="#tab-{$cMedienTypId}" aria-controls="tab-{$cMedienTypId}" role="tab" data-toggle="tab">
                            {$cMedienTyp}
                        </a>
                    </li>
                {/foreach}
            {/if}
            {if $useTags}
                <li role="presentation"
                    {if !$useVotes && !$setActive && !$useDescription && !$useDownloads && empty($separatedTabs)
                        && !$useQuestionOnItem && !$usePriceFlow && !$useAvailabilityNotification && !$useMediaGroup}
                    class="active"{/if}>
                    <a href="#tab-tags" aria-controls="tab-tags" role="tab" data-toggle="tab">
                        {lang key="productTags" section="productDetails"}
                    </a>
                </li>
            {/if}
        </ul>
    {/if}
    <div class="tab-content" id="article-tabs">
        {if $useDescription}
            {if $tabanzeige}
                <div role="tabpanel" class="tab-pane fade {if !$setActive} in active{/if}" id="tab-description">
            {else}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            {block name='tab-description-title'}{lang key='description' section='productDetails'}{/block}
                        </h3>
                    </div>
                    <div class="panel-body" id="tab-description">
            {/if}
            <div class="tab-content-wrapper">
                {block name="tab-description-content"}
                    <div class="desc">
                        {$Artikel->cBeschreibung}
                        {if $useMediaFiles && !empty($Artikel->cMedienTyp_arr)}
                            {foreach name="mediendateigruppen" from=$Artikel->cMedienTyp_arr item=cMedienTyp}
                                <div class="media">
                                    {include file='productdetails/mediafile.tpl'}
                                </div>
                            {/foreach}
                        {/if}
                    </div>
                {/block}
                {if (!empty($Artikel->cBeschreibung) || ($useMediaFiles && !empty($Artikel->cMedienTyp_arr)))
                    && $showAttributesTable}
                    <hr>
                {/if}
                {block name="tab-description-attributes"}
                    {include file="productdetails/attributes.tpl" tplscope="details"
                        showProductWeight=$showProductWeight showShippingWeight=$showShippingWeight
                        dimension=$dimension showAttributesTable=$showAttributesTable}
                {/block}
            </div>
            {if $tabanzeige}
                </div>
            {else}
                    </div>
                </div>
            {/if}
        {/if}
        {if $useDownloads}
            {if $tabanzeige}
                <div role="tabpanel" class="tab-pane fade {if !$setActive && !$useDescription} in active{/if}"
                    id="tab-downloads">
            {else}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{lang section="productDownloads" key="downloadSection"}</h3>
                    </div>
                    <div class="panel-body" id="tab-downloads">
            {/if}
            {include file="productdetails/download.tpl"}
            {if $tabanzeige}
                </div>
            {else}
                    </div>
                </div>
            {/if}
        {/if}
        {if !empty($separatedTabs)}
            {foreach from=$separatedTabs item=separatedTab name=separatedTabsHeader}
                {if $tabanzeige}
                    <div role="tabpanel" class="tab-pane fade
                        {if !$setActive && !$useDescription && !$useDownloads
                            && $smarty.foreach.separatedTabsHeader.first} in active{/if}"
                        id="tab-{$separatedTab.id}">
                {else}
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{$separatedTab.name}</h3>
                        </div>
                        <div class="panel-body" id="tab-{$separatedTab.id}">
                {/if}
                {$separatedTab.content}
                {if $tabanzeige}
                    </div>
                {else}
                        </div>
                    </div>
                {/if}
            {/foreach}
        {/if}
        {if $useVotes}
            {if $tabanzeige}
                <div role="tabpanel" class="tab-pane fade {if $setActive} in active{/if}" id="tab-votes">
            {else}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{lang section="productDownloads" key="downloadSection"}</h3>
                    </div>
                    <div class="panel-body" id="tab-votes">
            {/if}
            {include file="productdetails/reviews.tpl" stars=$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt}
            {if $tabanzeige}
                </div>
            {else}
                    </div>
                </div>
            {/if}
        {/if}
        {if $useQuestionOnItem}
            {if $tabanzeige}
                <div role="tabpanel" class="tab-pane fade
                    {if !$useVotes && !$setActive && !$useDescription && !$useDownloads && empty($separatedTabs)} in active{/if}"
                    id="tab-questionOnItem">
            {else}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{lang key="productQuestion" section="productDetails"}</h3>
                    </div>
                    <div class="panel-body" id="tab-questionOnItem">
            {/if}
            {include file="productdetails/question_on_item.tpl" position="tab"}
            {if $tabanzeige}
                </div>
            {else}
                    </div>
                </div>
            {/if}
        {/if}
        {if $usePriceFlow}
            {if $tabanzeige}
                <div role="tabpanel" class="tab-pane fade
                    {if !$useVotes && !$setActive && !$useDescription && !$useDownloads && empty($separatedTabs)
                        && !$useQuestionOnItem} in active{/if}" id="tab-priceFlow">
            {else}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{lang key="priceFlow" section="productDetails"}</h3>
                    </div>
                    <div class="panel-body" id="tab-priceFlow">
            {/if}
            {include file="productdetails/price_history.tpl"}
            {if $tabanzeige}
                </div>
            {else}
                    </div>
                </div>
            {/if}
        {/if}
        {if $useAvailabilityNotification}
            {if $tabanzeige}
                <div role="tabpanel" class="tab-pane fade
                    {if !$useVotes && !$setActive && !$useDescription && !$useDownloads && empty($separatedTabs)
                        && !$useQuestionOnItem && !$usePriceFlow} in active{/if}" id="tab-availabilityNotification">
            {else}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{lang key="notifyMeWhenProductAvailableAgain" section="global"}</h3>
                    </div>
                    <div class="panel-body" id="tab-availabilityNotification">
            {/if}
            {include file="productdetails/availability_notification_form.tpl" position="tab" tplscope="artikeldetails"}
            {if $tabanzeige}
                </div>
            {else}
                    </div>
                </div>
            {/if}
        {/if}
        {if $useMediaGroup}
            {foreach name="mediendateigruppen" from=$Artikel->cMedienTyp_arr item=cMedienTyp}
                {$cMedienTypId = $cMedienTyp|regex_replace:"/[\'\"\/ ]/":""}
                {if $tabanzeige}
                    <div role="tabpanel" class="tab-pane fade
                        {if !$useVotes && !$setActive && !$useDescription && !$useDownloads && empty($separatedTabs)
                            && !$useQuestionOnItem && !$usePriceFlow && !$useAvailabilityNotification
                            && $smarty.foreach.mediendateigruppen.first} in active{/if}" id="tab-{$cMedienTypId}">
                {else}
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{$cMedienTyp}</h3>
                        </div>
                        <div class="panel-body" id="tab-{$cMedienTypId}">
                {/if}
                {include file="productdetails/mediafile.tpl"}
                {if $tabanzeige}
                    </div>
                {else}
                        </div>
                    </div>
                {/if}
            {/foreach}
        {/if}
        {if $useTags}
            {if $tabanzeige}
                <div role="tabpanel" class="tab-pane fade
                    {if !$useVotes && !$setActive && !$useDescription && !$useDownloads && empty($separatedTabs)
                        && !$useQuestionOnItem && !$usePriceFlow && !$useAvailabilityNotification
                        && !$useMediaGroup} in active{/if}" id="tab-tags">
            {else}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{lang key="productTags" section="productDetails"}</h3>
                    </div>
                    <div class="panel-body" id="tab-tags">
            {/if}
            {include file="productdetails/tags.tpl"}
            {if $tabanzeige}
                </div>
            {else}
                    </div>
                </div>
            {/if}
        {/if}
    </div>
{/if}