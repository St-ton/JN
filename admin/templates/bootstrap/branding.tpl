{config_load file="$lang.conf" section='branding'}
{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('branding') cBeschreibung=__('brandingDesc') cDokuURL=__('brandingUrl')}
<div id="content" class="container-fluid">
    <div class="block">
        <form name="branding" method="post" action="branding.php">
            {$jtl_token}
            <input type="hidden" name="branding" value="1" />
            <div class="input-group p25 left">
                <span class="input-group-addon">
                    <label for="{__('brandingActive')}">{__('brandingPictureKat')}:</label>
                </span>
                <span class="input-group-wrap">
                    <select name="kBranding" class="form-control selectBox" id="{__('brandingActive')}" onchange="document.branding.submit();">
                        {foreach $oBranding_arr as $oBrandingTMP}
                            <option value="{$oBrandingTMP->kBranding}" {if $oBrandingTMP->kBranding == $oBranding->kBrandingTMP}selected{/if}>{$oBrandingTMP->cBildKategorie}</option>
                        {/foreach}
                    </select>
                </span>
            </div>
        </form>
    </div>

    {if $oBranding->kBrandingTMP > 0}
        <div class="no_overflow" id="settings">
            <form name="einstellen" method="post" action="branding.php" enctype="multipart/form-data">
                {$jtl_token}
                <input type="hidden" name="branding" value="1" />
                <input type="hidden" name="kBranding" value="{$oBranding->kBrandingTMP}" />
                <input type="hidden" name="speicher_einstellung" value="1" />
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Branding für {$oBranding->cBildKategorie} bearbeiten</h3>
                    </div>
                    <div class="panel-body">
                        {if $oBranding->cBrandingBild|strlen > 0}
                            <div class="thumbnail">
                                <img src="{$shopURL}/{$PFAD_BRANDINGBILDER}{$oBranding->cBrandingBild}?rnd={$cRnd}" alt="" />
                            </div>
                        {/if}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="nAktiv">{__('brandingActive')}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select name="nAktiv" id="nAktiv" class="form-control combo">
                                    <option value="1"{if $oBranding->nAktiv == 1} selected{/if}>{__('yes')}</option>
                                    <option value="0"{if $oBranding->nAktiv == 0} selected{/if}>{__('no')}</option>
                                </select>
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc=__('brandingActiveDesc')}</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cPosition">{__('brandingPosition')}</label>
                            </span>
                            <span class="input-group-wrap">
                                <select name="cPosition" id="cPosition" class="form-control combo">
                                    <option value="oben"{if $oBranding->cPosition === 'oben'} selected{/if}>
                                        {__('top')}
                                    </option>
                                    <option value="oben-rechts"{if $oBranding->cPosition === 'oben-rechts'} selected{/if}>
                                        {__('topRight')}
                                    </option>
                                    <option value="rechts"{if $oBranding->cPosition === 'rechts'} selected{/if}>
                                        {__('right')}
                                    </option>
                                    <option value="unten-rechts"{if $oBranding->cPosition === 'unten-rechts'} selected{/if}>
                                        {__('bottomRight')}
                                    </option>
                                    <option value="unten"{if $oBranding->cPosition === 'unten'} selected{/if}>
                                        {__('bottom')}
                                    </option>
                                    <option value="unten-links"{if $oBranding->cPosition === 'unten-links'} selected{/if}>
                                        {__('bottomLeft')}
                                    </option>
                                    <option value="links"{if $oBranding->cPosition === 'links'} selected{/if}>
                                        {__('left')}
                                    </option>
                                    <option value="oben-links"{if $oBranding->cPosition === 'oben-links'} selected{/if}>
                                        {__('topLeft')}
                                    </option>
                                    <option value="zentriert"{if $oBranding->cPosition === 'zentriert'} selected{/if}>
                                        {__('centered')}
                                    </option>
                                </select>
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc=__('brandingPositionDesc')}</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="dTransparenz">{__('brandingTransparency')}</label>
                            </span>
                            <span class="input-group-wrap">
                                <input class="form-control" type="text" name="dTransparenz" id="dTransparenz" value="{$oBranding->dTransparenz}" tabindex="1" />
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc=__('brandingTransparencyDesc')}</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="dGroesse">{__('brandingSize')}</label>
                            </span>
                            <span class="input-group-wrap">
                                <input class="form-control" type="text" name="dGroesse" id="dGroesse" value="{$oBranding->dGroesse}" tabindex="1" />
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc=__('brandingSizeDesc')}</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cBrandingBild">{__('brandingFileName')}</label>
                            </span>
                            <span class="input-group-wrap">
                                <input class="form-control" type="file" name="cBrandingBild" maxlength="2097152" accept="image/jpeg,image/gif,image/png,image/bmp" id="cBrandingBild" value="" tabindex="1" {if !$oBranding->cBrandingBild|strlen > 0}required{/if}/>
                            </span>
                            <span class="input-group-addon">{getHelpDesc cDesc=__('brandingFileNameDesc')}</span>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button type="submit" value="{__('save')}" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                    </div>
                </div>
            </form>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}
