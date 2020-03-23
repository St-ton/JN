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
                    {include file='tpl_inc/fileupload.tpl'
                    fileID='#shoplogo-upload'
                    fileUploadUrl="{$shopURL}/{$PFAD_ADMIN}shoplogouploader.php?token={$smarty.session.jtl_token}"
                    fileDeleteUrl="{$shopURL}/{$PFAD_ADMIN}shoplogouploader.php?token={$smarty.session.jtl_token}"
                    filePreview=$ShopLogo|strlen > 0
                    fileInitialPreview="[
                            '<img src=\"{$ShopLogoURL}\" class=\"file-preview-image img-fluid\" alt=\"Logo\" title=\"Logo\" />'
                        ]"
                    fileInitialPreviewConfig="[
                            {
                                url: '{$shopURL}/{$PFAD_ADMIN}shoplogouploader.php',
                                extra: {
                                action: 'deleteLogo',
                                logo: '{$ShopLogo}',
                                jtl_token: '{$smarty.session.jtl_token}'
                                }
                            }
                        ]"
                    }
                    <div class="mt-3">
                        <div id="shoplogo-upload-upload-success" class="alert alert-info hidden">{__('successLogoUpload')}</div>
                        <div id="shoplogo-upload-upload-error" class="alert alert-danger hidden">{{__('errorLogoUpload')}|sprintf:{$smarty.const.PFAD_SHOPLOGO}}</div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
