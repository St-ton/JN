{$tabanzeige = $Einstellungen.artikeldetails.artikeldetails_tabs_nutzen !== 'N'}
{if $Artikel->cBeschreibung|strlen > 0 || $Einstellungen.artikeldetails.merkmale_anzeigen === 'Y'
    && $Artikel->oMerkmale_arr|count > 1}
    {$tabsPaneleArr['description'] = [
        'id' => 'description',
        'cName' => "{block name='tab-description-title'}{lang key='description' section='productDetails'}{/block}",
        'content' => '<div class="desc">' ,
        'content2' => {$Artikel->cBeschreibung},
        'content3' => '',
        'content4' => '</div>',
        'content5' => "{block name='tab-description-attributes'}{include file='productdetails/attributes.tpl' tplscope='details'}{/block}"
    ]}
    {if (($Einstellungen.artikeldetails.mediendatei_anzeigen === 'YA' && $Artikel->cMedienDateiAnzeige !== 'tab')
        || $Artikel->cMedienDateiAnzeige === 'beschreibung') && !empty($Artikel->cMedienTyp_arr)}
        {foreach name="mediendateigruppen" from=$Artikel->cMedienTyp_arr item=cMedienTyp}
            {$mediaDescriptionContentArr[$cMedienTyp] = [
                'content' => '<div class="media">',
                'content2' => {include file='productdetails/mediafile.tpl'},
                'content3' => '</div>'
            ]}
        {/foreach}
        {$tabsPaneleArr.description.content3 = $mediaDescriptionContentArr}
    {/if}
{/if}
{if isset($Artikel->oDownload_arr) && $Artikel->oDownload_arr|@count > 0}
    {$tabsPaneleArr['downloads'] = [
    'id'      => 'downloads',
    'cName'   => {lang section="productDownloads" key="downloadSection"},
    'content' => {include file="productdetails/download.tpl"}
    ]}
{/if}
{section name=iterator start=1 loop=10}
    {$tab = tab}
    {$tabname = $tab|cat:$smarty.section.iterator.index|cat:" name"}
    {$tabinhalt = $tab|cat:$smarty.section.iterator.index|cat:" inhalt"}
    {if isset($Artikel->AttributeAssoc[$tabname]) && $Artikel->AttributeAssoc[$tabname]
        && $Artikel->AttributeAssoc[$tabinhalt]}
        {$tabsPaneleArr[{$tabname|replace:' ':'-'}] = [
            'id'      => {$tabname|replace:' ':'-'},
            'cName'   => {$Artikel->AttributeAssoc[$tabname]},
            'content' => {$Artikel->AttributeAssoc[$tabinhalt]}
        ]}
    {/if}
{/section}
{if $Einstellungen.bewertung.bewertung_anzeigen === 'Y'}
    {$tabsPaneleArr['votes'] = [
        'id'      => 'votes',
        'cName'   => {lang key="Votes" section="global"},
        'content' => {include file="productdetails/reviews.tpl" stars=$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt}
    ]}
{/if}
{if $Einstellungen.artikeldetails.artikeldetails_fragezumprodukt_anzeigen === 'Y'}
    {$tabsPaneleArr['questionOnItem'] = [
        'id'      => 'questionOnItem',
        'cName'   => {lang key="productQuestion" section="productDetails"},
        'content' => {include file="productdetails/question_on_item.tpl" position="tab"}
    ]}
{/if}
{if $Einstellungen.preisverlauf.preisverlauf_anzeigen === 'Y' && $bPreisverlauf}
    {$tabsPaneleArr['priceFlow'] = [
        'id'      => 'priceFlow',
        'cName'   => {lang key="priceFlow" section="productDetails"},
        'content' => {include file="productdetails/price_history.tpl"}
    ]}
{/if}
{if $verfuegbarkeitsBenachrichtigung == 1 && $Artikel->cLagerBeachten === 'Y'}
    {$tabsPaneleArr['availabilityNotification'] = [
        'id'      => 'availabilityNotification',
        'cName'   => {lang key="notifyMeWhenProductAvailableAgain" section="global"},
        'content' => {include file="productdetails/availability_notification_form.tpl" position="tab" tplscope="artikeldetails"}
    ]}
{/if}
{if (($Einstellungen.artikeldetails.mediendatei_anzeigen === 'YM' && $Artikel->cMedienDateiAnzeige !== 'beschreibung')
    || $Artikel->cMedienDateiAnzeige === 'tab') && !empty($Artikel->cMedienTyp_arr)}
    {foreach name="mediendateigruppen" from=$Artikel->cMedienTyp_arr item=cMedienTyp}
        {$cMedienTypId = $cMedienTyp|regex_replace:"/[\'\"\/ ]/":""}
        {$tabsPaneleArr[{$cMedienTypId}] = [
            'id'      => {$cMedienTypId},
            'cName'   => {$cMedienTyp},
            'content' => {include file="productdetails/mediafile.tpl"}
        ]}
    {/foreach}
{/if}
{if $Einstellungen.artikeldetails.tagging_anzeigen === 'Y' && (count($ProduktTagging) > 0
    || $Einstellungen.artikeldetails.tagging_freischaltung !== 'N')}
    {$tabsPaneleArr['tags'] = [
        'id'      => 'tags',
        'cName'   => {lang key="productTags" section="productDetails"},
        'content' => {include file="productdetails/tags.tpl"}
    ]}
{/if}

{if !empty($tabsPaneleArr)}
    {if $tabanzeige}
        <ul class="nav nav-tabs bottom15" role="tablist">
            {foreach from=$tabsPaneleArr item=tabPanel name=tabPanelItem}
                <li role="presentation"
                    {if $tabPanel.id === "votes" &&
                        (isset($smarty.get.ratings_nPage) && count($smarty.get.ratings_nPage) > 0
                        || isset($smarty.get.bewertung_anzeigen) && count($smarty.get.bewertung_anzeigen) > 0
                        || isset($smarty.get.ratings_nItemsPerPage) && count($smarty.get.ratings_nItemsPerPage) > 0
                        || isset($smarty.get.ratings_nSortByDir) && count($smarty.get.ratings_nSortByDir) > 0
                        || isset($smarty.get.btgsterne) && count($smarty.get.btgsterne) > 0)}
                        class="active"
                    {else}
                        {if $smarty.foreach.tabPanelItem.first && !isset($smarty.get.ratings_nPage)
                            && !isset($smarty.get.bewertung_anzeigen) && !isset($smarty.get.btgsterne)
                            && !isset($smarty.get.ratings_nItemsPerPage) && !isset($smarty.get.ratings_nSortByDir)}
                            class="active"
                        {/if}
                    {/if}>
                    <a href="#tab-{$tabPanel.id}" aria-controls="tab-{$tabPanel.id}" role="tab" data-toggle="tab">{$tabPanel.cName}</a>
                </li>
            {/foreach}
        </ul>
    {/if}
    <div class="tab-content" id="article-tabs">
        {foreach from=$tabsPaneleArr item=tabPanele name=tabPaneleItem}
            {if $tabanzeige}
                {if $tabPanele.id === "votes" &&
                    (isset($smarty.get.ratings_nPage) && count($smarty.get.ratings_nPage) > 0
                    || isset($smarty.get.bewertung_anzeigen) && count($smarty.get.bewertung_anzeigen) > 0
                    || isset($smarty.get.ratings_nItemsPerPage) && count($smarty.get.ratings_nItemsPerPage) > 0
                    || isset($smarty.get.ratings_nSortByDir) && count($smarty.get.ratings_nSortByDir) > 0
                    || isset($smarty.get.btgsterne) && count($smarty.get.btgsterne) > 0)}
                    <div role="tabpanel" class="tab-pane fade in active"
                {else}
                    <div role="tabpanel" class="tab-pane fade
                        {if $smarty.foreach.tabPaneleItem.first && !isset($smarty.get.ratings_nPage)
                            && !isset($smarty.get.bewertung_anzeigen) && !isset($smarty.get.btgsterne)
                            && !isset($smarty.get.ratings_nItemsPerPage)&& !isset($smarty.get.ratings_nSortByDir)} in active{/if}"
                {/if}
                     id="tab-{$tabPanele.id}">
            {else}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{$tabPanele.cName}</h3>
                    </div>
                    <div class="panel-body" id="tab-{$tabPanele.id}">
            {/if}
            {$tabPanele.content}
            {if !empty($tabPanele.content2)}
                {$tabPanele.content2}
            {/if}
            {if !empty($tabPanele.content3)}
                <div class="top15">
                    {foreach from=$tabPanele.content3 item=content3}
                        {$content3.content}
                        {$content3.content2}
                        {$content3.content3}
                    {/foreach}
                </div>
            {/if }
            {if !empty($tabPanele.content4)}
                {$tabPanele.content4}
            {/if}
            {if !empty($tabPanele.content5)}
                {$tabPanele.content5}
            {/if}
            {if $tabanzeige}
                </div>
            {else}
                    </div>
                </div>
            {/if}
        {/foreach}
    </div>
{/if}