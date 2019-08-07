{include file='tpl_inc/header.tpl' bForceFluid=($action === 'area')}
{config_load file="$lang.conf" section='banner'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('banner') cBeschreibung=__('bannerDesc') cDokuURL=__('bannerURL')}

<div id="content">
    {if $action === 'edit' || $action === 'new'}
    <script type="text/javascript">
        var file2large = false;

        function checkfile(e){
            e.preventDefault();
            if (!file2large){
                document.banner.submit();
            }
        }

        $(document).ready(function () {
            $('#nSeitenTyp').on('change', filterConfigUpdate);
            $('#cKey').on('change', filterConfigUpdate);

            filterConfigUpdate();

            $('form #oFile').on('change', function(e){
                $('form div.alert').slideUp();
                var filesize     = this.files[0].size;
                var maxsize      = {$nMaxFileSize};
                var errorMaxSize = "{__('errorUploadSizeLimit')}";
                if (filesize >= maxsize) {
                    $('.input-group.file-input')
                        .after('<div class="alert alert-danger"><i class="fal fa-exclamation-triangle"></i> ' + errorMaxSize + '</div>')
                        .slideDown();
                    file2large = true;
                } else {
                    $('form div.alert').slideUp();
                    file2large = false;
                }
            });

        });

        function filterConfigUpdate()
        {
            var $nSeitenTyp = $('#nSeitenTyp');
            var $type2      = $('#type2');
            var $nl         = $('.nl');
            var $cKey       = $('#cKey');

            $nl.hide();
            $('.key').hide();
            $type2.hide();

            switch ($nSeitenTyp.val()) {
                case '1':
                    $nl.show();
                    $('#keykArtikel').show();
                    $cKey.val('');
                    break;
                case '2':
                    $type2.show();
                    if ($cKey.val() !== '') {
                        $('#key' + $cKey.val()).show();
                        $nl.show();
                    }
                    break;
                case '31':
                    $nl.show();
                    $('#keykLink').show();
                    $cKey.val('');
                    break;
                default:
                    $cKey.val('');
                    break;
            }
        }
    </script>
    <div id="settings">
        <form name="banner" action="banner.php" method="post" enctype="multipart/form-data" onsubmit="checkfile(event);">
            {$jtl_token}
            <input type="hidden" name="action" value="{$action}" />
            {if $action === 'edit'}
                <input type="hidden" name="kImageMap" value="{$oBanner->kImageMap}" />
            {/if}

            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('general')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('internalName')} *:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" type="text" name="cName" id="cName" value="{if isset($cName)}{$cName}{elseif isset($oBanner->cTitel)}{$oBanner->cTitel}{/if}" />
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center file-input">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="oFile">{__('banner')} *:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <div class="custom-file">
                                <input class="custom-file-input" id="oFile" type="file" name="oFile" />
                                <label class="custom-file-label" for="oFile">
                                    <span class="text-truncate">{__('fileSelect')}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cPath">&raquo; {__('chooseAvailableFile')}:</label>
                        <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        {if $cBannerFile_arr|@count > 0}
                            <select id="cPath" name="cPath" class="custom-select">
                                <option value="">{__('chooseBanner')}</option>
                                {foreach $cBannerFile_arr as $cBannerFile}
                                    <option value="{$cBannerFile}" {if (isset($oBanner->cBildPfad) && $cBannerFile == $oBanner->cBildPfad) || (isset($oBanner->cBild) && $cBannerFile == $oBanner->cBild)}selected="selected"{/if}>{$cBannerFile}</option>
                                {/foreach}
                            </select>
                        {else}
                            {{__('warningNoBannerInDir')}|sprintf:{$cBannerLocation}}
                        {/if}
                        </span>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="vDatum">{__('activeFrom')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" type="text" name="vDatum" id="vDatum"/>
                        </div>
                        {include
                            file="snippets/daterange_picker.tpl"
                            datepickerID="#vDatum"
                            currentDate="{if isset($vDatum) && $vDatum > 0}{$vDatum|date_format:'%d.%m.%Y'}{elseif isset($oBanner->vDatum) && $oBanner->vDatum > 0}{$oBanner->vDatum|date_format:'%d.%m.%Y'}{/if}"
                            format="DD.MM.YYYY"
                            separator="{__('datepickerSeparator')}"
                            single=true
                        }
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="bDatum">{__('activeTo')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input class="form-control" type="text" name="bDatum" id="bDatum"  />
                        </div>
                        {include
                            file="snippets/daterange_picker.tpl"
                            datepickerID="#bDatum"
                            currentDate="{if isset($bDatum) && $bDatum > 0}{$bDatum|date_format:'%d.%m.%Y'}{elseif isset($oBanner->bDatum) && $oBanner->bDatum > 0}{$oBanner->bDatum|date_format:'%d.%m.%Y'}{/if}"
                            format="DD.MM.YYYY"
                            separator="{__('datepickerSeparator')}"
                            single=true
                        }
                    </div>
                </div><!-- /.panel-body -->
            </div><!-- /.panel -->

            {* extensionpoint begin *}

            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('viewingOptions')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="kSprache">{__('changeLanguage')}:</label>
                        <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select class="custom-select" id="kSprache" name="kSprache">
                                <option value="0">{__('all')}</option>
                                {foreach $oSprachen_arr as $language}
                                    <option value="{$language->getId()}" {if isset($kSprache) && $kSprache === $language->getId()}selected="selected" {elseif isset($oExtension->kSprache) && (int)$oExtension->kSprache === $language->getId()}selected="selected"{/if}>{$language->getLocalizedName()}</option>
                                {/foreach}
                            </select>
                        </span>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="kKundengruppe">{__('customerGroup')}:</label>
                        <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select class="custom-select" id="kKundengruppe" name="kKundengruppe">
                                <option value="0">{__('all')}</option>
                                {foreach $oKundengruppe_arr as $oKundengruppe}
                                    <option value="{$oKundengruppe->getID()}"
                                            {if isset($kKundengruppe) && $kKundengruppe == $oKundengruppe->getID()}selected="selected"
                                            {elseif isset($oExtension->kKundengruppe) && $oExtension->kKundengruppe == $oKundengruppe->getID()}selected="selected"{/if}
                                    >{$oKundengruppe->getName()}</option>
                                {/foreach}
                            </select>
                        </span>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="nSeitenTyp">{__('pageType')}:</label>
                        <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select class="custom-select" id="nSeitenTyp" name="nSeitenTyp">
                                {if isset($nSeitenTyp) && intval($nSeitenTyp) > 0}
                                    {include file='tpl_inc/seiten_liste.tpl' nPage=$nSeitenTyp}
                                {elseif isset($oExtension->nSeite)}
                                    {include file='tpl_inc/seiten_liste.tpl' nPage=$oExtension->nSeite}
                                {else}
                                    {include file='tpl_inc/seiten_liste.tpl' nPage=0}
                                {/if}
                            </select>
                        </span>
                    </div>
                    <div id="type2" class="custom">
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cKey">{__('filter')}</label>
                            <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select class="custom-select" id="cKey" name="cKey">
                                    <option value="" {if isset($oExtension->cKey) && $oExtension->cKey === ''}selected="selected"{/if}>
                                        {__('noFilter')}
                                    </option>
                                    <option value="kMerkmalWert" {if isset($cKey) && $cKey === 'kMerkmalWert'}selected="selected" {elseif isset($oExtension->cKey) && $oExtension->cKey === 'kMerkmalWert'}selected="selected"{/if}>
                                        {__('attribute')}
                                    </option>
                                    <option value="kKategorie" {if isset($cKey) && $cKey === 'kKategorie'}selected="selected" {elseif isset($oExtension->cKey) && $oExtension->cKey === 'kKategorie'}selected="selected"{/if}>
                                        {__('category')}
                                    </option>
                                    <option value="kHersteller" {if isset($cKey) && $cKey === 'kHersteller'}selected="selected" {elseif isset($oExtension->cKey) && $oExtension->cKey === 'kHersteller'}selected="selected"{/if}>
                                        {__('manufacturer')}
                                    </option>
                                    <option value="cSuche" {if isset($cKey) && $cKey === 'cSuche'}selected="selected" {elseif isset($oExtension->cKey) && $oExtension->cKey === 'cSuche'}selected="selected"{/if}>
                                        {__('searchTerm')}
                                    </option>
                                </select>
                            </span>
                        </div>
                    </div>
                    <div class="nl">
                        <div id="keykArtikel" class="form-group form-row align-items-center key">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="article_name">{__('product')}</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input type="hidden" name="article_key" id="article_key"
                                       value="{if (isset($cKey) && $cKey === 'kArtikel') || (isset($oExtension->cKey) && $oExtension->cKey === 'kArtikel')}{$oExtension->cValue}{/if}">
                                <input class="form-control" type="text" name="article_name" id="article_name">
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('typeAheadProduct')}</div>
                            <script>
                                enableTypeahead('#article_name', 'getProducts', 'cName', null, function(e, item) {
                                    $('#article_name').val(item.cName);
                                    $('#article_key').val(item.kArtikel);
                                });
                                {if (isset($cKey) && $cKey === 'kArtikel') || (isset($oExtension->cKey) && $oExtension->cKey === 'kArtikel')}
                                    ioCall('getProducts', [[$('#article_key').val()]], function (data) {
                                        $('#article_name').val(data[0].cName);
                                    });
                                {/if}
                            </script>
                        </div>
                        <div id="keykLink" class="form-group form-row align-items-center key">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="link_name">{__('pageSelf')}</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input type="hidden" name="link_key" id="link_key"
                                       value="{if (isset($cKey) && $cKey === 'kLink') || (isset($oExtension->cKey) && $oExtension->cKey === 'kLink')}{$oExtension->cValue}{/if}">
                                <input class="form-control" type="text" name="link_name" id="link_name">
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('typeAheadPage')}</div>
                            <script>
                                enableTypeahead('#link_name', 'getPages', 'cName', null, function(e, item) {
                                    $('#link_name').val(item.cName);
                                    $('#link_key').val(item.kLink);
                                });
                                {if (isset($cKey) && $cKey === 'kLink') || (isset($oExtension->cKey) && $oExtension->cKey === 'kLink')}
                                    ioCall('getPages', [[$('#link_key').val()]], function (data) {
                                        $('#link_name').val(data[0].cName);
                                    });
                                {/if}
                            </script>
                        </div>
                        <div id="keykMerkmalWert" class="form-group form-row align-items-center key">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="attribute_name">{__('attribute')}</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input type="hidden" name="attribute_key" id="attribute_key"
                                       value="{if (isset($cKey) && $cKey === 'kMerkmalWert') || (isset($oExtension->cKey) && $oExtension->cKey === 'kMerkmalWert')}{$oExtension->cValue}{/if}">
                                <input class="form-control" type="text" name="attribute_name" id="attribute_name">
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('typeAheadAttribute')}</div>
                            <script>
                                enableTypeahead('#attribute_name', 'getAttributes', 'cWert', null, function(e, item) {
                                    $('#attribute_name').val(item.cWert);
                                    $('#attribute_key').val(item.kMerkmalWert);
                                });
                                {if (isset($cKey) && $cKey === 'kMerkmalWert') || (isset($oExtension->cKey) && $oExtension->cKey === 'kMerkmalWert')}
                                    ioCall('getAttributes', [[$('#attribute_key').val()]], function (data) {
                                        $('#attribute_name').val(data[0].cWert);
                                    });
                                {/if}
                            </script>
                        </div>
                        <div id="keykKategorie" class="form-group form-row align-items-center key">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="categories_name">{__('category')}</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input type="hidden" name="categories_key" id="categories_key"
                                       value="{if (isset($cKey) && $cKey === 'kKategorie') || (isset($oExtension->cKey) && $oExtension->cKey === 'kKategorie')}{$oExtension->cValue}{/if}">
                                <input class="form-control" type="text" name="categories_name" id="categories_name">
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('typeAheadCategory')}</div>
                            <script>
                                enableTypeahead('#categories_name', 'getCategories', function(item) {
                                        var parentName = '';
                                        if (item.parentName !== null) {
                                            parentName = ' (' + item.parentName + ')';
                                        }
                                        return item.cName + parentName;
                                    }, null, function(e, item) {
                                    $('#categories_name').val(item.cName);
                                    $('#categories_key').val(item.kKategorie);
                                });
                                {if (isset($cKey) && $cKey === 'kKategorie') || (isset($oExtension->cKey) && $oExtension->cKey === 'kKategorie')}
                                    ioCall('getCategories', [[$('#categories_key').val()]], function (data) {
                                        $('#categories_name').val(data[0].cName);
                                    });
                                {/if}
                            </script>
                        </div>
                        <div id="keykHersteller" class="form-group form-row align-items-center key">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="manufacturer_name">{__('manufacturer')}</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input type="hidden" name="manufacturer_key" id="manufacturer_key"
                                       value="{if (isset($cKey) && $cKey === 'kHersteller') || (isset($oExtension->cKey) && $oExtension->cKey === 'kHersteller')}{$oExtension->cValue}{/if}">
                                <input class="form-control" type="text" name="manufacturer_name" id="manufacturer_name">
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('typeAheadManufacturer')}</div>
                            <script>
                                enableTypeahead('#manufacturer_name', 'getManufacturers', 'cName', null, function(e, item) {
                                    $('#manufacturer_name').val(item.cName);
                                    $('#manufacturer_key').val(item.kHersteller);
                                });
                                {if (isset($cKey) && $cKey === 'kHersteller') || (isset($oExtension->cKey) && $oExtension->cKey === 'kHersteller')}
                                    ioCall('getManufacturers', [[$('#manufacturer_key').val()]], function (data) {
                                        $('#manufacturer_name').val(data[0].cName);
                                    });
                                {/if}
                            </script>
                        </div>
                        <div id="keycSuche" class="key form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="ikeycSuche">{__('searchTerm')}</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control" type="text" id="ikeycSuche" name="keycSuche"
                                       value="{if (isset($cKey) &&  $cKey === 'cSuche') || (isset($oExtension->cKey) && $oExtension->cKey === 'cSuche')}{if isset($keycSuche) && $keycSuche !== ''}{$keycSuche}{else}{$oExtension->cValue}{/if}{/if}" />
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('enterSearchTerm')}</div>
                        </div>
                    </div>
                    {* extensionpoint end *}
                </div>
            </div>

            <div class="save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <a class="btn btn-outline-primary btn-block" href="banner.php">
                            {__('goBack')}
                        </a>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="submit" class="btn btn-primary btn-block" value="Banner speichern">
                            <i class="fa fa-save"></i> {__('saveBanner')}
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>
    {elseif $action === 'area'}
    <script type="text/javascript" src="{$shopURL}/includes/libs/flashchart/js/json/json2.js"></script>
    <script type="text/javascript" src="{$shopURL}/{$PFAD_ADMIN}/{$currentTemplateDir}js/clickareas.js"></script>
    <link rel="stylesheet" href="{$shopURL}/{$PFAD_ADMIN}/{$currentTemplateDir}css/clickareas.css" type="text/css" media="screen" />
    <script type="text/javascript">
        $(function () {ldelim}
            $.clickareas({ldelim}
                'id': '#area_wrapper',
                'editor': '#area_editor',
                'save': '#area_save',
                'add': '#area_new',
                'info': '#area_info',
                'data': {$oBanner|@json_encode nofilter}
            {rdelim});
        {rdelim});
    </script>
    <script type="text/javascript">
        {literal}
        $(document).ready(function () {
            $('#article_unlink').on('click', function () {
                $('#article_id').val(0);
                $('#article_name').val('');
                return false;
            });
        });
        {/literal}
    </script>
    <div class="category clearall">
        <div class="left">{__('zones')}</div>
        <div class="right" id="area_info"></div>
    </div>
    <div id="area_container">
        <div id="area_editor" class="card">
            <div class="category first card-header">
                <div class="subheading1">{__('settings')}</div>
            </div>
            <div id="settings" class="card-body">
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="title">{__('title')}</label>
                    </span>
                    <input class="form-control" type="text" id="title" name="title" />
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="desc">{__('description')}</label>
                    </span>
                    <textarea class="form-control" id="desc" name="desc"></textarea>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="url">{__('url')}</label>
                    </span>
                    <input class="form-control" type="text" id="url" name="url" />
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="style">{__('cssClass')}</label>
                    </span>
                    <input class="form-control" type="text" id="style" name="style" />
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="article_name">{__('product')}</label>
                    </span>
                    <input type="hidden" name="article" id="article" value="{if isset($oBanner->kArtikel)}{$oBanner->kArtikel}{/if}" />
                    <input type="text" name="article_name" id="article_name" value="" class="form-control">
                    <input type="hidden" name="article_id" id="article_id" value="">
                    <script>
                        enableTypeahead('#article_name', 'getProducts', 'cName', null, function (e, item) {
                            $('#article_name').val(item.cName);
                            $('#article_id').val(item.kArtikel);
                        });
                    </script>
                </div>
                <input type="hidden" name="id" id="id" />
                <div class="save-wrapper btn-group">
                    <a href="#" class="btn btn-default" id="article_browser">{__('chooseProduct')}</a>
                    <a href="#" class="btn btn-default" id="article_unlink">{__('deleteProduct')}</a>
                    <button type="button" class="btn btn-danger" id="remove"><i class="fas fa-trash-alt"></i> {__('zone')} {__('delete')}</button>
                </div>
            </div>
        </div>
        <div id="area_wrapper">
            <img class="img-fluid" src="{$oBanner->cBildPfad}" title="" id="clickarea" />
        </div>
    </div>
    <div class="save-wrapper">
        <div class="row">
            <div class="ml-auto col-sm-6 col-xl-auto">
                <a class="btn btn-outline-primary btn-block mb-2" href="banner.php" id="cancel">
                    {__('goBack')}
                </a>
            </div>
            <div class="col-sm-6 col-xl-auto">
                <a class="btn btn-outline-primary btn-block mb-2" href="#" id="area_new">
                    <i class="fa fa-share"></i> {__('new')} {__('zone')}
                </a>
            </div>
            <div class="col-sm-6 col-xl-auto">
                <a class="btn btn-primary btn-block" href="#" id="area_save">
                    <i class="fa fa-save"></i> {__('zones')} {__('save')}
                </a>
            </div>
        </div>
    </div>
    {else}
        <div id="settings">
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('availableBanner')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    {include file='tpl_inc/pagination.tpl' pagination=$pagination}
                    <div class="table-responsive">
                        <table class="list table">
                            <thead>
                            <tr>
                                <th class="text-left" width="25%">{__('name')}</th>
                                <th width="20%" class="text-center">{__('active')}</th>
                                <th class="text-left" width="25%">{__('runTime')}</th>
                                <th width="30%" class="text-center">{__('action')}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $oBanner_arr as $oBanner}
                                <tr>
                                    <td class="text-left">
                                        {$oBanner->cTitel}
                                    </td>
                                    <td class="text-center">
                                        {if (int)$oBanner->active === 1}
                                            <i class="fal fa-check text-success"></i>
                                        {else}
                                            <i class="fal fa-times text-danger"></i>
                                        {/if}
                                    </td>
                                    <td>
                                        {if $oBanner->vDatum !== null}
                                            {$oBanner->vDatum|date_format:'%d.%m.%Y'}
                                        {/if} -
                                        {if $oBanner->bDatum !== null}
                                            {$oBanner->bDatum|date_format:'%d.%m.%Y'}
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        <form action="banner.php" method="post">
                                            {$jtl_token}
                                            <input type="hidden" name="id" value="{$oBanner->kImageMap}" />
                                            <div class="btn-group">
                                                <button class="btn btn-link px-2" name="action" value="delete" title="{__('delete')}" data-toggle="tooltip">
                                                    <span class="icon-hover">
                                                        <span class="fal fa-trash-alt"></span>
                                                        <span class="fas fa-trash-alt"></span>
                                                    </span>
                                                </button>
                                                <button class="btn btn-link px-2" name="action" value="area" title="{__('actionLink')}" data-toggle="tooltip">
                                                    <span class="icon-hover">
                                                        <span class="fal fa-link"></span>
                                                        <span class="fas fa-link"></span>
                                                    </span>
                                                </button>
                                                <button class="btn btn-link px-2" name="action" value="edit" title="{__('edit')}" data-toggle="tooltip">
                                                    <span class="icon-hover">
                                                        <span class="fal fa-edit"></span>
                                                        <span class="fas fa-edit"></span>
                                                    </span>
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                {if $oBanner_arr|@count === 0}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
                </div>
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <a class="btn btn-primary btn-block" href="banner.php?action=new&token={$smarty.session.jtl_token}">
                                <i class="fa fa-share"></i> {__('addBanner')}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}
