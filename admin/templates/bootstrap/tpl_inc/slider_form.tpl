<div id="settings">
    <form action="slider.php?action={$cAction}" method="post" accept-charset="iso-8859-1" id="slider" enctype="multipart/form-data">
        {$jtl_token}
        <input type="hidden" name="action" value="{$cAction}" />
        <input type="hidden" name="kSlider" value="{$oSlider->getID()}" />
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{__('general')}</h3>
            </div>
            <div class="panel-body">
                <ul class="jtl-list-group">
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="cName">{__('internalName')}</label>
                        </div>
                        <div class="for">
                            <input type="text" name="cName" id="cName" class="form-control" value="{$oSlider->getName()}" />
                        </div>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="bAktiv">{__('status')}</label>
                        </div>
                        <div class="for">
                            <select id="bAktiv" name="bAktiv" class="form-control">
                                <option value="0"{if $oSlider->getIsActive() === false} selected="selected"{/if}>{__('deactivated')}</option>
                                <option value="1"{if $oSlider->getIsActive() === true} selected="selected"{/if}>{__('activated')}</option>
                            </select>
                        </div>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="bRandomStart">{__('randomStart')}</label>
                        </div>
                        <div class="for">
                            <select id="bRandomStart" name="bRandomStart" class="form-control">
                                <option value="0"{if $oSlider->getRandomStart() === false} selected="selected"{/if}>{__('no')}</option>
                                <option value="1"{if $oSlider->getRandomStart() === true} selected="selected"{/if}>{__('yes')}</option>
                            </select>
                        </div>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="bPauseOnHover">{__('pauseOnHover')}</label>
                        </div>
                        <div class="for">
                            <select id="bPauseOnHover" name="bPauseOnHover" class="form-control">
                                <option value="0"{if $oSlider->getPauseOnHover() === false} selected="selected"{/if}>{__('no')}</option>
                                <option value="1"{if $oSlider->getPauseOnHover() === true} selected="selected"{/if}>{__('yes')}</option>
                            </select>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{__('presentation')}</h3>
            </div>
            <div class="panel-body">
                <ul class="jtl-list-group">
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="bUseKB">{__('kenBurnsEffect')}</label>
                        </div>
                        <div class="for">
                            <select class="form-control" id="bUseKB" name="bUseKB">
                                <option value="0"{if $oSlider->getUseKB() === false} selected="selected"{/if}>{__('deactivated')}</option>
                                <option value="1"{if $oSlider->getUseKB() === true} selected="selected"{/if}>{__('activated')}</option>
                            </select>
                        </div>
                        <p><i class="fa fa-warning"></i> {__('overrideDescription')}</p>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="bControlNav">{__('navigation')}</label>
                        </div>
                        <div class="for">
                            <select class="form-control" id="bControlNav" name="bControlNav">
                                <option value="0"{if $oSlider->getControlNav() === false} selected="selected"{/if}>{__('hide')}
                                </option>
                                <option value="1"{if $oSlider->getControlNav() === true} selected="selected"{/if}>{__('show')}</option>
                            </select>
                        </div>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="bThumbnail">{__('thumbnail')} {__('navigation')}</label>
                        </div>
                        <div class="for">
                            <select class="form-control" id="bThumbnail" name="bThumbnail">
                                <option value="0"{if $oSlider->getThumbnail() === false} selected="selected"{/if}>{__('deactivated')}
                                </option>
                                <option value="1"{if $oSlider->getThumbnail() === true} selected="selected"{/if}>{__('activated')}</option>
                            </select>
                        </div>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="bDirectionNav">{__('navigation')} ({__('direction')})</label>
                        </div>
                        <div class="for">
                            <select class="form-control" id="bDirectionNav" name="bDirectionNav">
                                <option value="0"{if $oSlider->getDirectionNav() === false} selected="selected"{/if}>{__('hide')}</option>
                                <option value="1"{if $oSlider->getDirectionNav() === true} selected="selected"{/if}>{__('show')}</option>
                            </select>
                        </div>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <strong>{__('effects')}</strong>
                        </div>
                        <div class="for">
                            <input id="cRandomEffects" type="checkbox" value="random" class="random_effects" {if isset($checked)}{$checked} {/if}name="cEffects" />
                            <label for="cRandomEffects">{__('randomEffects')}</label>
                            <div class="select_container row">
                                <div class="col-xs-12 col-md-6 select_box">
                                    <label for="cSelectedEffects">{__('selectedEffects')}</label>
                                    <select class="form-control" id="cSelectedEffects" name="cSelectedEffects" size="10" multiple {$disabled}>
                                        {if isset($cEffects)}{$cEffects}{/if}
                                    </select>
                                    <input type="hidden" name="cEffects" value="{if isset($oSlider)}{$oSlider->getEffects()}{/if}" {$disabled}/>
                                    <button type="button" class="select_remove button remove btn btn-danger" value="entfernen" {$disabled}>{__('remove')}</button>
                                </div>
                                <div class="col-xs-12 col-md-6 select_box">
                                    <label for="cAvaibleEffects">{__('availableEffects')}</label>
                                    <select class="form-control" id="cAvaibleEffects" name="cAvaibleEffects" size="10" multiple {$disabled}>
                                        <option value="sliceDown">{__('sliceDown')}</option>
                                        <option value="sliceDownLeft">{__('sliceDownLeft')}</option>
                                        <option value="sliceUp">{__('sliceUp')}</option>
                                        <option value="sliceUpLeft">{__('sliceUpLeft')}</option>
                                        <option value="sliceUpDown">{__('sliceUpDown')}</option>
                                        <option value="sliceUpDownLeft">{__('sliceUpDownLeft')}</option>
                                        <option value="fold">{__('fold')}</option>
                                        <option value="fade">{__('fade')}</option>
                                        <option value="slideInRight">{__('slideInRight')}</option>
                                        <option value="slideInLeft">{__('slideInLeft')}</option>
                                        <option value="boxRandom">{__('boxRandom')}</option>
                                        <option value="boxRain">{__('boxRain')}</option>
                                        <option value="boxRainReverse">{__('boxRainReverse')}</option>
                                        <option value="boxRainGrow">{__('boxRainGrow')}</option>
                                        <option value="boxRainGrowReverse">{__('boxRainGrowReverse')}</option>
                                    </select>
                                    <button type="button" class="select_add button add btn btn-default" value="hinzufügen" {$disabled}>{__('add')}</button>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="cTheme">{__('theme')}</label>
                        </div>
                        <div class="for">
                            <select id="cTheme" name="cTheme" class="form-control">
                                <option value="default"{if $oSlider->getTheme() === 'default'} selected="selected"{/if}>{__('default')}</option>
                                <option value="bar"{if $oSlider->getTheme() === 'bar'} selected="selected"{/if}>{__('bar')}</option>
                                <option value="light"{if $oSlider->getTheme() === 'light'} selected="selected"{/if}>{__('light')}</option>
                                <option value="dark"{if $oSlider->getTheme() === 'dark'} selected="selected"{/if}>{__('dark')}</option>
                            </select>
                        </div>
                    </li>

                    <li class="list-group-item item">
                        <div class="name">
                            <label for="nAnimationSpeed">{__('animationSpeed')}</label>
                        </div>
                        <div class="for">
                            <input type="text" name="nAnimationSpeed" id="nAnimationSpeed" value="{$oSlider->getAnimationSpeed()}" class="form-control" />
                            <p id="nAnimationSpeedWarning" class="nAnimationSpeedWarningColor">{__('warningAnimationTimeLower')}</p>
                        </div>
                    </li>

                    <li class="list-group-item item">
                        <div class="name">
                            <label for="nPauseTime">{__('pauseTime')}</label>
                        </div>
                        <div class="for">
                            <input type="text" name="nPauseTime" id="nPauseTime" value="{$oSlider->getPauseTime()}" class="form-control" />
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{__('displayOptions')}</h3>
            </div>
            <div class="panel-body">
                <ul class="jtl-list-group">
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="kSprache">{__('language')}</label>
                        </div>
                        <div class="for">
                            <select id="kSprache" name="kSprache" class="form-control">
                                <option value="0">{__('all')}</option>
                                {foreach $oSprachen_arr as $oSprache}
                                    <option value="{$oSprache->kSprache}" {if isset($oExtension->kSprache) && $oExtension->kSprache == $oSprache->kSprache}selected="selected"{/if}>{$oSprache->name}</option>
                                {/foreach}
                            </select>
                        </div>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="kKundengruppe">{__('customerGroup')}</label>
                        </div>
                        <div class="for">
                            <select id="kKundengruppe" name="kKundengruppe" class="form-control">
                                <option value="0">{__('all')}</option>
                                {foreach $oKundengruppe_arr as $oKundengruppe}
                                    <option value="{$oKundengruppe->getID()}" {if isset($oExtension->kKundengruppe) && $oExtension->kKundengruppe == $oKundengruppe->getID()}selected="selected"{/if}>{$oKundengruppe->getName()}</option>
                                {/foreach}
                            </select>
                        </div>
                    </li>
                    <li class="list-group-item item">
                        <div class="name">
                            <label for="nSeitenTyp">{__('pageType')}</label>
                        </div>
                        <div class="for">
                            {if isset($oExtension->nSeite)}
                                <select class="form-control" id="nSeitenTyp" name="nSeitenTyp"> {include file='tpl_inc/seiten_liste.tpl' nPage=$oExtension->nSeite}</select>
                            {else}
                                <select class="form-control" id="nSeitenTyp" name="nSeitenTyp"> {include file='tpl_inc/seiten_liste.tpl' nPage=0}</select>
                            {/if}
                        </div>
                        <div id="type2" class="custom">
                            <div class="item">
                                <div class="name">
                                    <label for="cKey">{__('filter')}</label>
                                </div>
                                <div class="for">
                                    <select class="form-control" name="cKey" id="cKey">
                                        <option value="" {if isset($oExtension->cKey) && $oExtension->cKey === ''} selected="selected"{/if}>
                                            {__('noFilter')}
                                        </option>
                                        <option value="kTag" {if isset($oExtension->cKey) && $oExtension->cKey === 'kTag'} selected="selected"{/if}>
                                            {__('tag')}
                                        </option>
                                        <option value="kMerkmalWert" {if isset($oExtension->cKey) && $oExtension->cKey === 'kMerkmalWert'} selected="selected"{/if}>
                                            {__('attribute')}
                                        </option>
                                        <option value="kKategorie" {if isset($oExtension->cKey) && $oExtension->cKey === 'kKategorie'} selected="selected"{/if}>
                                            {__('category')}
                                        </option>
                                        <option value="kHersteller" {if isset($oExtension->cKey) && $oExtension->cKey === 'kHersteller'} selected="selected"{/if}>
                                            {__('manufacturer')}
                                        </option>
                                        <option value="cSuche" {if isset($oExtension->cKey) && $oExtension->cKey === 'cSuche'} selected="selected"{/if}>
                                            {__('searchTerm')}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="nl list-group-item item">
                        <div id="keykArtikel" class="key">
                            <div class="name"><label for="article_name">{__('product')}</label></div>
                            <input type="hidden" name="article_key" id="article_key"
                                   value="{if (isset($cKey) && $cKey === 'kArtikel') || (isset($oExtension->cKey) && $oExtension->cKey === 'kArtikel')}{$oExtension->cValue}{/if}">
                            <div class="input-group">
                                <input class="form-control" type="text" name="article_name" id="article_name">
                                <span class="input-group-addon">{getHelpDesc cDesc=__('typeAheadProduct')}</span>
                            </div>
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
                        <div id="keykLink" class="key">
                            <div class="name"><label for="link_name">{__('pageSelf')}</label></div>
                            <input type="hidden" name="link_key" id="link_key"
                                   value="{if (isset($cKey) && $cKey === 'kLink') || (isset($oExtension->cKey) && $oExtension->cKey === 'kLink')}{$oExtension->cValue}{/if}">
                            <div class="input-group">
                                <input class="form-control" type="text" name="link_name" id="link_name">
                                <span class="input-group-addon">{getHelpDesc cDesc=__('typeAheadPages')}</span>
                            </div>
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
                        <div id="keykTag" class="input-group key">
                            <div class="name"><label for="tag_name">Tag</label></div>
                            <input type="hidden" name="tag_key" id="tag_key"
                                   value="{if (isset($cKey) && $cKey === 'kTag') || (isset($oExtension->cKey) && $oExtension->cKey === 'kTag')}{$oExtension->cValue}{/if}">
                            <div class="input-group">
                                <input class="form-control" type="text" name="tag_name" id="tag_name">
                                <span class="input-group-addon">{getHelpDesc cDesc=__('typeAheadTag')}</span>
                            </div>
                            <script>
                                enableTypeahead('#tag_name', 'getTags', 'cName', null, function(e, item) {
                                    $('#tag_name').val(item.cName);
                                    $('#tag_key').val(item.kTag);
                                });
                                {if (isset($cKey) && $cKey === 'kTag') || (isset($oExtension->cKey) && $oExtension->cKey === 'kTag')}
                                    ioCall('getTags', [[$('#tag_key').val()]], function (data) {
                                        $('#tag_name').val(data[0].cName);
                                    });
                                {/if}
                            </script>
                        </div>
                        <div id="keykMerkmalWert" class="input-group key">
                            <div class="name"><label for="attribute_name">{__('attribute')}</label></div>
                            <input type="hidden" name="attribute_key" id="attribute_key"
                                   value="{if (isset($cKey) && $cKey === 'kMerkmalWert') || (isset($oExtension->cKey) && $oExtension->cKey === 'kMerkmalWert')}{$oExtension->cValue}{/if}">
                            <div class="input-group">
                                <input class="form-control" type="text" name="attribute_name" id="attribute_name">
                                <span class="input-group-addon">{getHelpDesc cDesc=__('typeAheadAttribute')}</span>
                            </div>
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
                        <div id="keykKategorie" class="key">
                            <div class="name"><label for="categories_name">{__('category')}</label></div>
                            <input type="hidden" name="categories_key" id="categories_key"
                                   value="{if (isset($cKey) && $cKey === 'kKategorie') || (isset($oExtension->cKey) && $oExtension->cKey === 'kKategorie')}{$oExtension->cValue}{/if}">
                            <div class="input-group">
                                <input class="form-control" type="text" name="categories_name" id="categories_name">
                                <span class="input-group-addon">{getHelpDesc cDesc=__('typeAheadCategory')}</span>
                            </div>
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
                        <div id="keykHersteller" class="input-group key">
                            <div class="name"><label for="manufacturer_name">{__('manufacturer')}</label></div>
                            <input type="hidden" name="manufacturer_key" id="manufacturer_key"
                                   value="{if (isset($cKey) && $cKey === 'kHersteller') || (isset($oExtension->cKey) && $oExtension->cKey === 'kHersteller')}{$oExtension->cValue}{/if}">
                            <div class="input-group">
                                <input class="form-control" type="text" name="manufacturer_name" id="manufacturer_name">
                                <span class="input-group-addon">{getHelpDesc cDesc=__('typeAheadAttribute')}</span>
                            </div>
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
                        <div id="keycSuche" class="key input-group">
                            <div class="name"><label for="ikeycSuche">{__('searchTerm')}</label></div>
                            <div class="input-group">
                                <input class="form-control" type="text" id="ikeycSuche" name="keycSuche"
                                       value="{if (isset($cKey) &&  $cKey === 'cSuche') || (isset($oExtension->cKey) && $oExtension->cKey === 'cSuche')}{if isset($keycSuche) && $keycSuche !== ''}{$keycSuche}{else}{$oExtension->cValue}{/if}{/if}">
                                <span class="input-group-addon">{getHelpDesc cDesc=__('enterSearchTerm')}</span>
                            </div>
                            </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="save_wrapper btn-group">
            <button type="submit" class="btn btn-primary" value="{__('save')}"><i class="fa fa-save"></i> {__('save')}</button>
            <button type="button" class="btn btn-default" onclick="window.location.href = 'slider.php';" value="zurück"><i class="fa fa-angle-double-left"></i> {__('back')}</button>
        </div>
    </form>
</div>
