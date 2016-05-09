{include file='tpl_inc/seite_header.tpl' cTitel=#newsCat#}
<div id="content">
    <form name="news" method="post" action="news.php">
        {$jtl_token}
        <input type="hidden" name="news" value="1" />
        <input type="hidden" name="news_kategorie_speichern" value="1" />
        <input type="hidden" name="tab" value="kategorien" />
        {if isset($oNewsKategorie->kNewsKategorie) && $oNewsKategorie->kNewsKategorie > 0}
            <input type="hidden" name="newskategorie_edit_speichern" value="1" />
            <input type="hidden" name="kNewsKategorie" value="{$oNewsKategorie->kNewsKategorie}" />
        {/if}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{if isset($oNewsKategorie->kNewsKategorie) && $oNewsKategorie->kNewsKategorie > 0}{#newsCatNew#}{else}{#newsCatAdd#}{/if}</h3>
            </div>

            <table class="list table" id="formtable">
                <tr>
                    <td><label for="cName">{#newsCatName#}</label></td>
                    <td>
                        <input class="form-control" id="cName" name="cName" type="text" value="{if isset($cPostVar_arr.cName)}{$cPostVar_arr.cName}{elseif isset($oNewsKategorie->cName)}{$oNewsKategorie->cName}{/if}" />{if isset($cPlausiValue_arr.cName) && $cPlausiValue_arr.cName == 2} {#newsAlreadyExists#}{/if}
                    </td>
                </tr>
                <tr>
                    <td><label for="cSeo">{#newsSeo#}</label></td>
                    <td>
                        <input class="form-control" id="cSeo" name="cSeo" type="text" value="{if isset($cPostVar_arr.cSeo)}{$cPostVar_arr.cSeo}{elseif isset($oNewsKategorie->cSeo)}{$oNewsKategorie->cSeo}{/if}" />
                    </td>
                </tr>
                <tr>
                    <td><label for="nSort">{#newsCatSort#}</label></td>
                    <td>
                        <input class="form-control" id="nSort" name="nSort" type="text" value="{if isset($cPostVar_arr.nSort)}{$cPostVar_arr.nSort}{elseif isset($oNewsKategorie->nSort)}{$oNewsKategorie->nSort}{/if}" style="width: 60px;" />
                    </td>
                </tr>
                <tr>
                    <td><label for="nAktiv">{#newsActive#}</label></td>
                    <td>
                        <select class="form-control" id="nAktiv" name="nAktiv">
                            <option value="1"{if (isset($cPostVar_arr.nAktiv) && $cPostVar_arr.nAktiv == "1") || (isset($oNewsKategorie->nAktiv) && $oNewsKategorie->nAktiv == 1)} selected{/if}>
                                Ja
                            </option>
                            <option value="0"{if (isset($cPostVar_arr.nAktiv) && $cPostVar_arr.nAktiv == "0") || (isset($oNewsKategorie->nAktiv) && $oNewsKategorie->nAktiv == 0)} selected{/if}>
                                Nein
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="cMetaTitle">{#newsMetaTitle#}</label></td>
                    <td>
                        <input class="form-control" id="cMetaTitle" name="cMetaTitle" type="text" value="{if isset($cPostVar_arr.cMetaTitle)}{$cPostVar_arr.cMetaTitle}{elseif isset($oNewsKategorie->cMetaTitle)}{$oNewsKategorie->cMetaTitle}{/if}" />
                    </td>
                </tr>
                <tr>
                    <td><label for="cMetaDescription">{#newsMetaDescription#}</label></td>
                    <td>
                        <input class="form-control" id="cMetaDescription" name="cMetaDescription" type="text" value="{if isset($cPostVar_arr.cMetaDescription)}{$cPostVar_arr.cMetaDescription}{elseif isset($oNewsKategorie->cMetaDescription)}{$oNewsKategorie->cMetaDescription}{/if}" />
                    </td>
                </tr>
                <tr>
                    <td><label for="cBeschreibung">{#newsCatDesc#}</label></td>
                    <td>
                        <textarea id="cBeschreibung" class="ckeditor" name="cBeschreibung" rows="15" cols="60">{if isset($cPostVar_arr.cBeschreibung)}{$cPostVar_arr.cBeschreibung}{elseif isset($oNewsKategorie->cBeschreibung)}{$oNewsKategorie->cBeschreibung}{/if}</textarea>
                    </td>
                </tr>
            </table>
            <div class="panel-footer">
                <button name="speichern" type="button" value="{#newsSave#}" onclick="document.news.submit();" class="btn btn-primary"><i class="fa fa-save"></i> {#newsSave#}</button>
            </div>
        </div>
    </form>
</div>