{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="banner"}
{include file='tpl_inc/seite_header.tpl' cTitel=#banner# cBeschreibung=#bannerDesc# cDokuURL=#bannerURL#}

<div id="content">
    {if $cFehler}
        {if isset($cPlausi_arr.vDatum)}
            <div class="alert alert-danger">{if $cPlausi_arr.vDatum == 1}Konnte Ihre Eingabe f&uuml;r das 'Aktiv von Datum' nicht verarbeiten.{/if}</div>
        {/if}
        {if isset($cPlausi_arr.bDatum)}
            <div class="alert alert-danger">
                {if $cPlausi_arr.bDatum == 1}
                    Konnte Ihre Eingabe f&uuml;r das 'Aktiv bis Datum' nicht verarbeiten.
                {elseif $cPlausi_arr.bDatum == 2}
                    Das Datum bis wann ein Banner aktiv ist muss gr&ouml;&szlig;er sein als das 'Aktiv von Datum'.
                {/if}
            </div>
        {/if}
        {if isset($cPlausi_arr.oFile)}
            <div class="alert alert-danger"><i class="fa fa-warning"></i> Die Bilddatei ist zu gro&szlig;.</div>
        {/if}
    {/if}

    {if $cAction === 'edit' || $cAction === 'new'}
    <script type="text/javascript">
        {literal}
        $(document).ready(function () {
            $("select[name='nSeitenTyp']").change(function () {
                var selected = $("select[name='nSeitenTyp'] option:selected");
                typeChanged($(selected).val());
            }).change();

            $("select[name='cKey']").change(function () {
                var selected = $("select[name='cKey'] option:selected");
                keyChanged($(selected).val());
            }).change();

            $('.nl').find('a').each(function () {
                var type = $(this).attr('id');
                $(this).click(function () {
                    show_simple_search(type);
                });
            });

            init_simple_search(function (type, res) {
                $(".nl input[name='" + type + "_key']").val(res.kKey);
                $(".nl input[name='" + type + "_name']").val(res.cName);
            });


            $('form #oFile').change(function(e){
                $('form div.alert').slideUp();
                var filesize= this.files[0].size;
                {/literal}
                var maxsize = {$nMaxFileSize};
                {literal}
                if (filesize >= maxsize) {
                    $('.input-group.file-input').after('<div class="alert alert-danger"><i class="fa fa-warning"></i> Die Datei ist gr&ouml;&szlig;er als das Uploadlimit des Servers.</div>').slideDown();
                } else {
                    $('form div.alert').slideUp();
                }
            });

        });

        function typeChanged(type) {
            $('.custom').hide();
            $('#type' + type).show();

            if (type != 2) {
                $('select[name="cKey"]').val('');
                $('.nl .key').hide();
                $('.nl input[type="text"], .nl input[type="hidden"]').each(function () {
                    $(this).val('');
                });
            }
        }

        function keyChanged(key) {
            // reset keys
            $('.key[id!="key' + key + '"]').find('input').each(function () {
                $(this).val('');
            });

            $('.key').hide();
            $('#key' + key).show();
        }


        {/literal}
    </script>
    <div id="settings">
        <form action="banner.php" method="post" enctype="multipart/form-data">
            {$jtl_token}
            <input type="hidden" name="action" value="{$cAction}" />
            {if $cAction === 'edit'}
                <input type="hidden" name="kImageMap" value="{$oBanner->kImageMap}" />
            {/if}

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Allgemein</h3>
                </div>
                <div class="panel-body">
                    <div class="input-group">
                        <span class="input-group-addon"><label for="cName">Interner Name *</label></span>
                        <input class="form-control" type="text" name="cName" id="cName" value="{if isset($cName)}{$cName}{elseif isset($oBanner->cTitel)}{$oBanner->cTitel}{/if}" />
                    </div>
                    <div class="input-group file-input">
                        <span class="input-group-addon"><label for="oFile">Banner *</label></span>
                        <input class="form-control" id="oFile" type="file" name="oFile" />
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="cPath">&raquo; vorhandene Datei w&auml;hlen</label></span>
                        <span class="input-group-wrap">
                        {if $cBannerFile_arr|@count > 0}
                            <select id="cPath" name="cPath" class="form-control">
                                <option value="">Banner w&auml;hlen</option>
                                {foreach from=$cBannerFile_arr item=cBannerFile}
                                    <option value="{$cBannerFile}" {if (isset($oBanner->cBildPfad) && $cBannerFile == $oBanner->cBildPfad) || (isset($oBanner->cBild) && $cBannerFile == $oBanner->cBild)}selected="selected"{/if}>{$cBannerFile}</option>
                                {/foreach}
                            </select>
                        {else}
                            Kein Banner im Ordner <strong>{$cBannerLocation}</strong> vorhanden
                        {/if}
                        </span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="vDatum">Aktiv von</label></span>
                        <input class="form-control" type="text" name="vDatum" id="vDatum" value="{if isset($vDatum) && $vDatum > 0}{$vDatum|date_format:"%d.%m.%Y"}{elseif isset($oBanner->vDatum) && $oBanner->vDatum > 0}{$oBanner->vDatum|date_format:"%d.%m.%Y"}{/if}" />
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="bDatum">Aktiv bis</label></span>
                        <input class="form-control" type="text" name="bDatum" id="bDatum" value="{if isset($bDatum) && $bDatum > 0}{$bDatum|date_format:"%d.%m.%Y"}{elseif isset($oBanner->bDatum) && $oBanner->bDatum > 0}{$oBanner->bDatum|date_format:"%d.%m.%Y"}{/if}" />
                    </div>
                </div><!-- /.panel-body -->
            </div><!-- /.panel -->

            {* extensionpoint begin *}

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Anzeigeoptionen</h3>
                </div>
                <div class="panel-body">
                    <div class="input-group">
                        <span class="input-group-addon"><label for="kSprache">Sprache</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" id="kSprache" name="kSprache">
                                <option value="0">Alle</option>
                                {foreach from=$oSprachen_arr item=oSprache}
                                    <option value="{$oSprache->kSprache}" {if isset($kSprache) && $kSprache == $oSprache->kSprache}selected="selected" {elseif isset($oExtension->kSprache) && $oExtension->kSprache == $oSprache->kSprache}selected="selected"{/if}>{$oSprache->cNameDeutsch}</option>
                                {/foreach}
                            </select>
                        </span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="kKundengruppe">Kundengruppe</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" id="kKundengruppe" name="kKundengruppe">
                                <option value="0">Alle</option>
                                {foreach from=$oKundengruppe_arr item=oKundengruppe}
                                    <option value="{$oKundengruppe->getKundengruppe()}" {if isset($kKundengruppe) && $kKundengruppe == $oKundengruppe->getKundengruppe()}selected="selected" {elseif isset($oExtension->kKundengruppe) && $oExtension->kKundengruppe == $oKundengruppe->getKundengruppe()}selected="selected"{/if}>{$oKundengruppe->getName()}</option>
                                {/foreach}
                            </select>
                        </span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="nSeitenTyp">Seitentyp</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" id="nSeitenTyp" name="nSeitenTyp">
                                {if isset($nSeitenTyp) && intval($nSeitenTyp) > 0}
                                    {include file="tpl_inc/seiten_liste.tpl" nPage=$nSeitenTyp}
                                {elseif isset($oExtension->nSeite)}
                                    {include file="tpl_inc/seiten_liste.tpl" nPage=$oExtension->nSeite}
                                {else}
                                    {include file="tpl_inc/seiten_liste.tpl" nPage=0}
                                {/if}
                            </select>
                        </span>
                    </div>
                    <div id="type2" class="custom">
                        <div class="input-group">
                            <span class="input-group-addon"><label for="cKey">&raquo; Filter</label></span>
                            <div>
                                <span class="input-group-wrap">
                                    <select class="form-control" id="cKey" name="cKey">
                                        <option value="" {if isset($oExtension->cKey) && $oExtension->cKey === ''}selected="selected"{/if}>
                                            Kein Filter
                                        </option>
                                        <option value="kTag" {if isset($cKey) && $cKey === 'kTag'}selected="selected" {elseif isset($oExtension->cKey) && $oExtension->cKey === 'kTag'}selected="selected"{/if}>
                                            Tag
                                        </option>
                                        <option value="kMerkmalWert" {if isset($cKey) && $cKey === 'kMerkmalWert'}selected="selected" {elseif isset($oExtension->cKey) && $oExtension->cKey === 'kMerkmalWert'}selected="selected"{/if}>
                                            Merkmal
                                        </option>
                                        <option value="kKategorie" {if isset($cKey) && $cKey === 'kKategorie'}selected="selected" {elseif isset($oExtension->cKey) && $oExtension->cKey === 'kKategorie'}selected="selected"{/if}>
                                            Kategorie
                                        </option>
                                        <option value="kHersteller" {if isset($cKey) && $cKey === 'kHersteller'}selected="selected" {elseif isset($oExtension->cKey) && $oExtension->cKey === 'kHersteller'}selected="selected"{/if}>
                                            Hersteller
                                        </option>
                                        <option value="cSuche" {if isset($cKey) && $cKey === 'cSuche'}selected="selected" {elseif isset($oExtension->cKey) && $oExtension->cKey === 'cSuche'}selected="selected"{/if}>
                                            Suchbegriff
                                        </option>
                                    </select>
                                </span>
                            </div>
                        </div>
                    </div>
                    {include file="tpl_inc/single_search_browser.tpl"}
                    <div class="nl">
                        <div id="keykTag" class="key">
                            <input type="hidden" name="tag_key" value="{if (isset($cKey) && $cKey === 'kTag') || (isset($oExtension->cKey) && $oExtension->cKey === 'kTag')}{$oExtension->cValue}{/if}" />
                            <input class="form-control" type="text" name="tag_name" disabled="disabled" value="{if (isset($cKey) && $cKey === 'kTag') || (isset($oExtension->cKey) && $oExtension->cKey === 'kTag')}{if isset($tag_key) && $tag_key !== ''}{$tag_key}{elseif isset($oExtension->cValue) && $oExtension->cValue !== ''}{$oExtension->cValue}{else}Kein Tag ausgew&auml;hlt{/if}{/if}" />
                            <a href="#" class="btn btn-success" id="tag">Tag suchen</a>
                        </div>
                        <div id="keykMerkmalWert" class="key">
                            <input type="hidden" name="attribute_key" value="{if (isset($cKey) && $cKey === 'kMerkmalWert') || (isset($oExtension->cKey) && $oExtension->cKey === 'kMerkmalWert')}{$oExtension->cValue}{/if}" />
                            <input class="form-control" type="text" name="attribute_name" disabled="disabled" value="{if (isset($cKey) && $cKey === 'kMerkmalWert') || (isset($oExtension->cKey) && $oExtension->cKey == 'kMerkmalWert')}{if isset($attribute_key) && $attribute_key !== ''}{$attribute_key}{elseif isset($oExtension->cValue) && $oExtension->cValue !== ''}{$oExtension->cValue}{else}Kein Merkmal ausgew&auml;hlt{/if}{/if}" />
                            <a href="#" class="btn btn-success" id="attribute">Merkmal suchen</a>
                        </div>
                        <div id="keykKategorie" class="key">
                            <input type="hidden" name="categories_key" value="{if (isset($cKey) && $cKey === 'kKategorie') || (isset($oExtension->cKey) && $oExtension->cKey === 'kKategorie')}{$oExtension->cValue}{/if}" />
                            <input class="form-control" type="text" name="categories_name" disabled="disabled" value="{if (isset($cKey) && $cKey === 'kKategorie') || (isset($oExtension->cKey) && $oExtension->cKey === 'kKategorie')}{if isset($categories_key) && $categories_key !== ''}{$categories_key}{elseif isset($oExtension->cValue) && $oExtension->cValue !== ''}{$oExtension->cValue}{else}Keine Kategorie ausgew&auml;hlt{/if}{/if}" />
                            <a href="#" class="btn btn-success" id="categories">Kategorie suchen</a>
                        </div>
                        <div id="keykHersteller" class="key">
                            <input type="hidden" name="manufacturer_key" value="{if (isset($cKey) && $cKey === 'kHersteller') || (isset($oExtension->cKey) && $oExtension->cKey === 'kHersteller')}{$oExtension->cValue}{/if}" />
                            <input class="form-control" type="text" name="manufacturer_name" disabled="disabled" value="{if (isset($cKey) && $cKey === 'kHersteller') || (isset($oExtension->cKey) && $oExtension->cKey === 'kHersteller')}{if isset($manufacturer_key) && $manufacturer_key !== ''}{$manufacturer_key}{elseif isset($oExtension->cValue) && $oExtension->cValue !== ''}{$oExtension->cValue}{else}Kein Hersteller ausgew&auml;hlt{/if}{/if}" />
                            <a href="#" class="btn btn-success" id="manufacturer">Hersteller suchen</a>
                        </div>
                        <div id="keycSuche" class="key input-group">
                            <span class="input-group-addon"><label for="ikeycSuche">Suchbegriff</label></span>
                            <input class="form-control" type="text" id="ikeycSuche" name="keycSuche" value="{if (isset($cKey) &&  $cKey === 'cSuche') || (isset($oExtension->cKey) && $oExtension->cKey === 'cSuche')}{if isset($keycSuche) && $keycSuche !== ''}{$keycSuche}{else}{$oExtension->cValue}{/if}{/if}" />
                        </div>
                    </div>
                    {* extensionpoint end *}
                </div>
            </div>

            <div class="save_wrapper">
                <button type="submit" class="btn btn-primary" value="Banner speichern"><i class="fa fa-save"></i> Banner speichern</button>
            </div>

        </form>
    </div>
    {elseif $cAction == 'area'}
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
            $('#article_browser').click(function () {
                show_simple_search('article');
                return false;
            });

            init_simple_search(function (type, res) {
                $('#article').val(res.kPrimary);
                $('#article_info').html((res.kPrimary > 0) ? '<span class="success">Verkn&uuml;pft</span>' : '<span class="error">Nicht verkn&uuml;pft</span>');
            });

            $('#article_unlink').click(function () {
                $('#article').val(0);
                $('#article_info').html('<span class="error">Nicht verkn&uuml;pft</span>');
                return false;
            });
        });
        {/literal}
    </script>
    <div class="category clearall">
        <div class="left">Zonen</div>
        <div class="right" id="area_info"></div>
    </div>
    {include file="tpl_inc/single_search_browser.tpl"}
    <div id="area_container">
        <div id="area_editor" class="panel panel-default">
            <div class="category first panel-heading">
                <h3 class="panel-title">Einstellungen</h3>
            </div>
            <div id="settings" class="panel-body">
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="title">Titel</label>
                    </span>
                    <input class="form-control" type="text" id="title" name="title" />
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="desc">Beschreibung</label>
                    </span>
                    <textarea class="form-control" id="desc" name="desc"></textarea>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="url">Url</label>
                    </span>
                    <input class="form-control" type="text" id="url" name="url" />
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="style">CSS-Klasse</label>
                    </span>
                    <input class="form-control" type="text" id="style" name="style" />
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="article">Artikel</label>
                    </span>
                    <span class="input-group-wrap">
                        <span id="article_info" style="margin-left:5px;"></span>
                    </span>
                    <input type="hidden" name="article" id="article" value="{if isset($oBanner->kArtikel)}{$oBanner->kArtikel}{/if}" />
                </div>
                <a href="#" class="btn btn-default" id="article_browser">Artikel w&auml;hlen</a>
                <a href="#" class="btn btn-default" id="article_unlink">Artikel L&ouml;sen</a>

                <input type="hidden" name="id" id="id" />
                <div class="save_wrapper btn-group">
                    <button type="button" class="btn btn-danger" id="remove"><i class="fa fa-trash"></i>Zone l&ouml;schen</button>
                </div>
            </div>
        </div>
        <div id="area_wrapper">
            <img src="{$oBanner->cBildPfad}" title="" id="clickarea" />
        </div>
    </div>
    <div class="save_wrapper btn-group">
        <a class="btn btn-default" href="#" id="area_new"><i class="fa fa-share"></i> Neue Zone</a>
        <a class="btn btn-primary" href="#" id="area_save"><i class="fa fa-save"></i> Zonen speichern</a>
        <a class="btn btn-danger" href="banner.php" id="cancel"><i class="fa fa-angle-double-left"></i> zur&uuml;ck</a>
    </div>
    {else}
        <div id="settings">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Vorhandene Banner</h3>
                </div>
                <table class="list table">
                    <thead>
                    <tr>
                        <th class="tleft" width="50%">Name</th>
                        <th width="20%">Status</th>
                        <th width="30%">Aktionen</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach name="banner" from=$oBanner_arr item=oBanner}
                        <tr>
                            <td class="tleft">
                                {$oBanner->cTitel}
                            </td>
                            <td class="tcenter">
                                <h4 class="label-wrap">
                                    <span class="label success label-success">aktiv</span>
                                </h4>
                            </td>
                            <td class="tcenter">
                                <form action="banner.php" method="post">
                                    {$jtl_token}
                                    <input type="hidden" name="id" value="{$oBanner->kImageMap}" />
                                    <div class="btn-group">
                                        <button class="btn btn-default" name="action" value="area" title="verlinken"><i class="fa fa-link"></i></button>
                                        <button class="btn btn-default" name="action" value="edit" title="bearbeiten"><i class="fa fa-edit"></i></button>
                                        <button class="btn btn-danger" name="action" value="delete" title="entfernen"><i class="fa fa-trash"></i></button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>

                {if $oBanner_arr|@count === 0}
                   <div class="panel-body">
                       <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                   </div>
                {/if}
                <div class="panel-footer">
                    <a class="btn btn-primary" href="banner.php?action=new&token={$smarty.session.jtl_token}"><i class="fa fa-share"></i> Banner hinzuf&uuml;gen</a>
                </div>
            </div>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}