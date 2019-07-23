{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='bilder'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('imageTitle') cBeschreibung=__('bilderDesc') cDokuURL=__('bilderURL')}
<div id="content">
    <form method="post" action="bilder.php">
        {$jtl_token}
        <input type="hidden" name="speichern" value="1">
        <div id="settings">
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('imageSizes')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="table-responsive card-body">
                    <table class="list table table-border-light table-images">
                        <thead>
                        <tr>
                            <th class="tleft">{__('type')}</th>
                            <th class="tcenter">{__('xs')}<small>{__('widthXHeight')}</small></th>
                            <th class="tcenter">{__('sm')}<small>{__('widthXHeight')}</small></th>
                            <th class="tcenter">{__('md')}<small>{__('widthXHeight')}</small></th>
                            <th class="tcenter">{__('lg')}<small>{__('widthXHeight')}</small></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="tleft">{__('category')}</td>
                            <td class="tcenter"></td>
                            <td class="tcenter"></td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_kategorien_breite" value="{$oConfig.bilder_kategorien_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_kategorien_hoehe" value="{$oConfig.bilder_kategorien_hoehe}" />
                            </td>
                            <td class="tcenter"></td>
                        </tr>

                        <tr>
                            <td class="tleft">{__('variations')}</td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_variationen_mini_breite" value="{$oConfig.bilder_variationen_mini_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_variationen_mini_hoehe" value="{$oConfig.bilder_variationen_mini_hoehe}" />
                            </td>
                            <td class="tcenter"></td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_variationen_breite" value="{$oConfig.bilder_variationen_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_variationen_hoehe" value="{$oConfig.bilder_variationen_hoehe}" />
                            </td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_variationen_gross_breite" value="{$oConfig.bilder_variationen_gross_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_variationen_gross_hoehe" value="{$oConfig.bilder_variationen_gross_hoehe}" />
                            </td>
                        </tr>

                        <tr>
                            <td class="tleft">{__('product')}</td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_artikel_mini_breite" value="{$oConfig.bilder_artikel_mini_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_artikel_mini_hoehe" value="{$oConfig.bilder_artikel_mini_hoehe}" />
                            </td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_artikel_klein_breite" value="{$oConfig.bilder_artikel_klein_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_artikel_klein_hoehe" value="{$oConfig.bilder_artikel_klein_hoehe}" />
                            </td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_artikel_normal_breite" value="{$oConfig.bilder_artikel_normal_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_artikel_normal_hoehe" value="{$oConfig.bilder_artikel_normal_hoehe}" />
                            </td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_artikel_gross_breite" value="{$oConfig.bilder_artikel_gross_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_artikel_gross_hoehe" value="{$oConfig.bilder_artikel_gross_hoehe}" />
                            </td>
                        </tr>

                        <tr>
                            <td class="tleft">{__('manufacturer')}</td>
                            <td class="tcenter"></td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_hersteller_klein_breite" value="{$oConfig.bilder_hersteller_klein_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_hersteller_klein_hoehe" value="{$oConfig.bilder_hersteller_klein_hoehe}" />
                            </td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_hersteller_normal_breite" value="{$oConfig.bilder_hersteller_normal_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_hersteller_normal_hoehe" value="{$oConfig.bilder_hersteller_normal_hoehe}" />
                            </td>
                            <td class="tcenter"></td>
                        </tr>

                        <tr>
                            <td class="tleft">{__('attributes')}</td>
                            <td class="tcenter"></td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_merkmal_klein_breite" value="{$oConfig.bilder_merkmal_klein_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_merkmal_klein_hoehe" value="{$oConfig.bilder_merkmal_klein_hoehe}" />
                            </td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_merkmal_normal_breite" value="{$oConfig.bilder_merkmal_normal_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_merkmal_normal_hoehe" value="{$oConfig.bilder_merkmal_normal_hoehe}" />
                            </td>
                            <td class="tcenter"></td>
                        </tr>

                        <tr>
                            <td class="tleft">{__('attributeValues')}</td>
                            <td class="tcenter"></td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_merkmalwert_klein_breite" value="{$oConfig.bilder_merkmalwert_klein_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_merkmalwert_klein_hoehe" value="{$oConfig.bilder_merkmalwert_klein_hoehe}" />
                            </td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_merkmalwert_normal_breite" value="{$oConfig.bilder_merkmalwert_normal_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_merkmalwert_normal_hoehe" value="{$oConfig.bilder_merkmalwert_normal_hoehe}" />
                            </td>
                            <td class="tcenter"></td>
                        </tr>

                        <tr>
                            <td class="tleft">{__('configGroup')}</td>
                            <td class="tcenter"></td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_konfiggruppe_klein_breite" value="{$oConfig.bilder_konfiggruppe_klein_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_konfiggruppe_klein_hoehe" value="{$oConfig.bilder_konfiggruppe_klein_hoehe}" />
                            </td>
                            <td class="tcenter"></td>
                            <td class="tcenter"></td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>
            {assign var=open value=false}
            {foreach $oConfig_arr as $cnf}
            {if $cnf->kEinstellungenConf == 267 || $cnf->kEinstellungenConf == 268 || $cnf->kEinstellungenConf == 269 || $cnf->kEinstellungenConf == 1135 || $cnf->kEinstellungenConf == 1421 || $cnf->kEinstellungenConf == 172 || $cnf->kEinstellungenConf == 161  || $cnf->kEinstellungenConf == 1483  || $cnf->kEinstellungenConf == 1484 || $cnf->kEinstellungenConf == 1485}
                {if $cnf->cConf === 'Y'}
                    <div class="form-group form-row align-items-center{if isset($cSuche) && $cnf->kEinstellungenConf == $cSuche} highlight{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right order-1" for="{$cnf->cWertName}">{$cnf->cName}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        {if $cnf->cInputTyp === 'selectbox'}
                            <select class="custom-select" name="{$cnf->cWertName}" id="{$cnf->cWertName}">
                                {foreach $cnf->ConfWerte as $wert}
                                    <option value="{$wert->cWert}" {if $cnf->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                {/foreach}
                            </select>
                        {elseif $cnf->cInputTyp === 'pass'}
                            <input class="form-control" type="password" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" />
                        {elseif $cnf->cInputTyp === 'number'}
                            <input class="form-control" type="number" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" />
                        {elseif $cnf->cInputTyp === 'color'}
                            <span class="input-group-colorpicker-wrap">
                            <div id="{$cnf->cWertName}" style="display:inline-block">
                                <div style="background-color: {$cnf->gesetzterWert}" class="colorSelector"></div>
                            </div>
                            <input type="hidden" name="{$cnf->cWertName}" class="{$cnf->cWertName}_data" value="{$cnf->gesetzterWert}" />
                            <script type="text/javascript">
                                $('#{$cnf->cWertName}').ColorPicker({ldelim}
                                    color:    '{$cnf->gesetzterWert}',
                                    onShow:   function (colpkr) {ldelim}
                                        $(colpkr).fadeIn(500);
                                        return false;
                                    {rdelim},
                                    onHide:   function (colpkr) {ldelim}
                                        $(colpkr).fadeOut(500);
                                        return false;
                                    {rdelim},
                                    onChange: function (hsb, hex, rgb) {ldelim}
                                        $('#{$cnf->cWertName} div').css('backgroundColor', '#' + hex);
                                        $('.{$cnf->cWertName}_data').val('#' + hex);
                                    {rdelim}
                                {rdelim});
                            </script>
                            </span>
                        {else}
                            <input class="form-control" type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" />
                        {/if}
                        </div>
                        {if $cnf->cBeschreibung}
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                {getHelpDesc cDesc=$cnf->cBeschreibung cID=$cnf->kEinstellungenConf}
                            </div>
                        {/if}
                    </div>
                {else}
                    {if $open}</div></div>{/if}
                    <div class="card">
                        <div class="card-header">
                            <div class="subheading1">{$cnf->cName}
                            <span class="float-right">{getHelpDesc cID=$cnf->kEinstellungenConf}</span>
                            {if isset($cnf->cSektionsPfad) && $cnf->cSektionsPfad|strlen > 0}
                                <span class="path"><strong>{__('settingspath')}:</strong> {$cnf->cSektionsPfad}</span>
                            {/if}
                            </div>
                            <hr class="mb-n3">
                        </div>
                        <div class="card-body">
                        {assign var=open value=true}
                {/if}
            {/if}
            {/foreach}
            {if $open}
                </div><!-- /.panel-body -->
            </div><!-- /.panel -->
            {/if}
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto submit">
                        <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                            {__('saveWithIcon')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
