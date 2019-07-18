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
                    <script>
                        $('#shoplogo-upload').fileinput({ldelim}
                            uploadUrl: '{$shopURL}/{$PFAD_ADMIN}shoplogouploader.php?token={$smarty.session.jtl_token}',
                            allowedFileExtensions : ['jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp'],
                            overwriteInitial: true,
                            deleteUrl: '{$shopURL}/{$PFAD_ADMIN}shoplogouploader.php?token={$smarty.session.jtl_token}',
                            initialPreviewCount: 1,
                            uploadAsync: false,
                            showPreview: true,
                            language: 'de',
                            maxFileSize: 100000,
                            maxFilesNum: 1,
                            fileActionSettings: {ldelim}
                                showDrag: false
                            {rdelim}{if $ShopLogo|strlen > 0},
                            initialPreviewConfig: [
                                {ldelim}
                                    url: '{$shopURL}/{$PFAD_ADMIN}shoplogouploader.php',
                                    extra: {ldelim}logo: '{$ShopLogo}'{rdelim}
                                {rdelim}
                            ],
                            initialPreview: [
                                '<img src="{$ShopLogoURL}" class="file-preview-image" alt="Logo" title="Logo" />'
                            ]
                            {/if}
                        {rdelim}).on('fileuploaded', function(event, data) {ldelim}
                            if (data.response.status === 'OK') {ldelim}
                                $('#logo-upload-success').show().removeClass('hidden');
                                $('.kv-upload-progress').addClass('hide');
                            {rdelim} else {ldelim}
                                $('#logo-upload-error').show().removeClass('hidden');
                            {rdelim}
                        {rdelim});
                    </script>
                    <div id="logo-upload-success" class="alert alert-info hidden">{__('successLogoUpload')}</div>
                    <div id="logo-upload-error" class="alert alert-danger hidden">{{__('errorLogoUpload')}|sprintf:{$smarty.const.PFAD_SHOPLOGO}}</div>
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
