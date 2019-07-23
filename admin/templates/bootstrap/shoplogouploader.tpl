{config_load file="$lang.conf" section='shoplogouploader'}
{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('shoplogouploader') cBeschreibung=__('shoplogouploaderDesc') cDokuURL=__('shoplogouploaderURL')}
<div id="content">
    <form name="uploader" method="post" action="shoplogouploader.php" enctype="multipart/form-data">
        {$jtl_token}
        <div class="card">
            <div class="card-header">
                <span class="subheading1">{__('yourLogo')}</span>
            </div>
            <div class="card-body">
                <input type="hidden" name="upload" value="1" />
                <div class="col-xs-12">
                    <input name="shopLogo" id="shoplogo-upload" type="file" class="file" accept="image/*">
                    {if isset($language) && $language === 'de-DE'}
                        {assign var=uploaderLang value='de'}
                    {else}
                        {assign var=uploaderLang value='LANG'}
                    {/if}
                    <script>
                        $('#shoplogo-upload').fileinput({
                            uploadUrl: '{$shopURL}/{$PFAD_ADMIN}shoplogouploader.php?token={$smarty.session.jtl_token}',
                            showUpload: false,
                            showRemove: false,
                            showCancel: false,
                            uploadAsync: false,
                            showPreview: true,
                            fileActionSettings: {
                                showZoom: false,
                                showRemove: false,
                                showDrag: false
                            },
                            allowedFileExtensions : ['jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp'],
                            overwriteInitial: true,
                            deleteUrl: '{$shopURL}/{$PFAD_ADMIN}shoplogouploader.php?token={$smarty.session.jtl_token}',
                            initialPreviewCount: 1,
                            theme: 'fas',
                            language: '{$uploaderLang}',
                            browseOnZoneClick:     true,
                            maxFileSize: 100000,
                            maxFilesNum: 1,
                            {if $ShopLogo|strlen > 0}
                            initialPreviewConfig: [
                                {
                                    url: '{$shopURL}/{$PFAD_ADMIN}shoplogouploader.php',
                                    extra: { logo: '{$ShopLogo}' }
                                }
                            ],
                            initialPreview: [
                                '<img src="{$ShopLogoURL}" class="file-preview-image" alt="Logo" title="Logo" />'
                            ]
                            {/if}
                        }).on("filebrowse", function(event, files) {
                            $('#shoplogo-upload').fileinput('clear');
                        }).on("filebatchselected", function(event, files) {
                            $('#shoplogo-upload').fileinput("upload");
                        }).on('filebatchuploadsuccess', function(event, data) {
                            if (data.response.status === 'OK') {
                                $('#logo-upload-success').show().removeClass('hidden');
                            } else {
                                $('#logo-upload-error').show().removeClass('hidden');
                            }
                        });
                    </script>
                    <div id="logo-upload-success" class="alert alert-info hidden">{__('successLogoUpload')}</div>
                    <div id="logo-upload-error" class="alert alert-danger hidden">{{__('errorLogoUpload')}|sprintf:{$smarty.const.PFAD_SHOPLOGO}}</div>
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
