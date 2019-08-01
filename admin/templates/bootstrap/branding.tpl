{config_load file="$lang.conf" section='branding'}
{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('branding') cBeschreibung=__('brandingDesc') cDokuURL=__('brandingUrl')}
<div id="content">
    <div class="card">
        <div class="card-body">
            <form name="branding" method="post" action="branding.php">
                {$jtl_token}
                <input type="hidden" name="branding" value="1" />
                <div class="form-row">
                    <label class="col-sm-auto col-form-label" for="{__('brandingActive')}">{__('brandingPictureKat')}:</label>
                    <span class="col-sm-auto">
                        <select name="kBranding" class="custom-select selectBox" id="{__('brandingActive')}" onchange="document.branding.submit();">
                            {foreach $oBranding_arr as $oBrandingTMP}
                                <option value="{$oBrandingTMP->kBranding}" {if $oBrandingTMP->kBranding == $oBranding->kBrandingTMP}selected{/if}>{__($oBrandingTMP->cBildKategorie)}</option>
                            {/foreach}
                        </select>
                    </span>
                </div>
            </form>
        </div>
    </div>

    {if $oBranding->kBrandingTMP > 0}
        <div class="no_overflow" id="settings">
            <form name="einstellen" method="post" action="branding.php" enctype="multipart/form-data">
                {$jtl_token}
                <input type="hidden" name="branding" value="1" />
                <input type="hidden" name="kBranding" value="{$oBranding->kBrandingTMP}" />
                <input type="hidden" name="speicher_einstellung" value="1" />
                <div class="card">
                    <div class="card-header">
                        <div class="subheading1">{{__('headingEditBrandingForProduct')}|sprintf:{$oBranding->cBildKategorie}}</div>
                        <hr class="mb-n3">
                    </div>
                    <div class="card-body">
                        {if $oBranding->cBrandingBild|strlen > 0}
                            <div class="thumbnail">
                                <img class="img-fluid" src="{$shopURL}/{$PFAD_BRANDINGBILDER}{$oBranding->cBrandingBild}?rnd={$cRnd}" alt="" />
                            </div>
                        {/if}
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nAktiv">{__('brandingActive')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select name="nAktiv" id="nAktiv" class="custom-select combo">
                                    <option value="1"{if $oBranding->nAktiv == 1} selected{/if}>{__('yes')}</option>
                                    <option value="0"{if $oBranding->nAktiv == 0} selected{/if}>{__('no')}</option>
                                </select>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('brandingActiveDesc')}</div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cPosition">{__('position')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select name="cPosition" id="cPosition" class="custom-select combo">
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
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('brandingPositionDesc')}</div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="dTransparenz">{__('transparency')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <div class="input-group form-counter min-w-sm">
                                    <div class="input-group-prepend">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                            <span class="fas fa-minus"></span>
                                        </button>
                                    </div>
                                    <input class="form-control" type="number" name="dTransparenz" id="dTransparenz" value="{$oBranding->dTransparenz}" tabindex="1" />
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                            <span class="fas fa-plus"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('brandingTransparencyDesc')}</div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="dGroesse">{__('size')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <div class="input-group form-counter min-w-sm">
                                    <div class="input-group-prepend">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                            <span class="fas fa-minus"></span>
                                        </button>
                                    </div>
                                    <input class="form-control" type="number" name="dGroesse" id="dGroesse" value="{$oBranding->dGroesse}" tabindex="1" />
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                            <span class="fas fa-plus"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('brandingSizeDesc')}</div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cBrandingBild">{__('brandingFileName')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <div class="input-group mb-3">
                                    <div class="custom-file">
                                        <input class="custom-file-input" type="file" name="cBrandingBild" maxlength="2097152" accept="image/*" id="cBrandingBild" value="" tabindex="1" {if !$oBranding->cBrandingBild|strlen > 0}required{/if}/>
                                        <label class="custom-file-label" for="cBrandingBild">
                                            <span class="text-truncate">{__('fileSelect')}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('brandingFileNameDesc')}</div>
                        </div>
                    </div>
                    <div class="card-footer save-wrapper">
                        <div class="row">
                            <div class="ml-auto col-sm-6 col-xl-auto submit">
                                <button type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                                    {__('saveWithIcon')}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}
