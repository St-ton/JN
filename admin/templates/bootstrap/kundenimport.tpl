{include file='tpl_inc/header.tpl'}

{if !empty($noPasswordCustomerIds)}
    <script>
        let noPasswordCustomerIds = {json_encode($noPasswordCustomerIds)};
        let totalCustomerIds      = noPasswordCustomerIds.length;
        let numCustomersProcessed = 0;

        function notifyCustomers()
        {
            let notifyCustomersBtn  = $('#notifyCustomersBtn');
            let notifyCustomersInfo = $('#notifyCustomersInfo');
            let chunk = noPasswordCustomerIds.splice(0, 10);
            notifyCustomersBtn.prop('disabled', true);

            notifyCustomersInfo.html(
                '{__('customersNotified')}: ' + numCustomersProcessed + ' {__('of')} ' + totalCustomerIds
            );

            if (chunk.length > 0) {
                ioCall('notifyImportedCustomers', [chunk], () => {
                    numCustomersProcessed += chunk.length;
                    notifyCustomers();
                });
            } else {
                notifyCustomersBtn.html('{__('customersNotifiedOK')}');
            }
        }
    </script>
    <div class="alert alert-warning align-items-center" data-fade-out="0" data-key="importNotifyModal">
        <div class="row mr-0">
            <div class="col">
                <p>
                    <i class="fal fa-exclamation-triangle mr-2"></i>
                    <span id="notifyCustomersInfo">{__('notifyCustomersInfo')}</span>
                </p>
                <button id="notifyCustomersBtn" type="submit" name="action" value="notify-customers"
                        class="btn btn-secondary"
                        onclick="notifyCustomers()">
                    {__('notifyCustomersButton')}
                </button>
            </div>
            <div class="col-auto ml-auto">
                <div class="close">&times;</div>
            </div>
        </div>
    </div>
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=__('customerImport') cBeschreibung=__('customerImportDesc')
    cDokuURL=__('customerImportURL')}
<div id="content">
    <form method="post" action="{$adminURL}{$route}" enctype="multipart/form-data">
        {$jtl_token}
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
                            {foreach $availableLanguages as $language}
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
                                <option value="{$kundengruppe->kKundengruppe}">{$kundengruppe->cName}</option>
                            {/foreach}
                        </select>
                    </span>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="csv">{__('csvFile')}:</label>
                    <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        {include file='tpl_inc/fileupload.tpl'
                            fileID='csv'
                            fileAllowedExtensions="['csv','txt']"
                            fileShowRemove=true
                            fileMaxSize=false
                            fileRequired=true
                        }
                    </span>
                    <style>
                        .krajee-default.file-preview-frame .kv-file-content, .kv-preview-data.file-preview-text {
                            width: 100%!important;
                            max-width: 100%!important;
                        }
                    </style>
                </div>
            </div>
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <button type="submit" name="action" value="import-customers"
                                class="btn btn-primary btn-block">
                            {__('import')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
