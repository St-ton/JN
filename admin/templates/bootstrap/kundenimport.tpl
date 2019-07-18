{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='kundenimport'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('customerImport') cBeschreibung=__('customerImportDesc') cDokuURL=__('customerImportURL')}
<div id="content">
    <form name="kundenimporter" method="post" action="kundenimport.php" enctype="multipart/form-data">
        {$jtl_token}
        <input type="hidden" name="kundenimport" value="1" />
        <div class="settings card">
            <div class="card-header">
                <div class="subheading1">{__('customerImport')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="kSprache">{__('language')}:</label>
                    <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select name="kSprache" id="kSprache" class="custom-select combo">
                            {foreach $sprachen as $language}
                                <option value="{$language->getId()}">{$language->getLocalizedName()}</option>
                            {/foreach}
                        </select>
                    </span>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="kKundengruppe">{__('customerGroup')}:</label>
                    <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select name="kKundengruppe" id="kKundengruppe" class="custom-select combo">
                            {foreach $kundengruppen as $kundengruppe}
                                {assign var=kKundengruppe value=$kundengruppe->kKundengruppe}
                                <option value="{$kundengruppe->kKundengruppe}">{$kundengruppe->cName}</option>
                            {/foreach}
                        </select>
                    </span>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="PasswortGenerieren">{__('generateNewPass')}:</label>
                    <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select name="PasswortGenerieren" id="PasswortGenerieren" class="custom-select comboFullSize">
                            <option value="0">{__('passNo')}</option>
                            <option value="1">{__('passYes')}</option>
                        </select>
                    </span>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="csv">{__('csvFile')}:</label>
                    <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" type="file" name="csv" id="csv" tabindex="1" />
                    </span>
                </div>
            </div>
            <div class="card-footer save-wrapper">
                <button type="submit" value="{__('import')}" class="btn btn-primary">{__('import')}</button>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
