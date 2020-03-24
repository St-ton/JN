{*
-----------------------------------------------------------------------------------
variable name             | default | description
-----------------------------------------------------------------------------------
$fileID                        |         | id of file input
$fileAllowedExtensions         | ----->  | default: ['jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'svg']
$fileUploadUrl                 | false   | url to upload file via ajax
$fileDeleteUrl                 |         | url to delete file via ajax
$filePreview                   | false   | enable previe image
$fileMaxSize                   |         | max allowed size of file
$fileIsSingle                  | true    | only allow one file to be uploaded
$fileInitialPreviewConfig      |         | array with json - config of initial preview
$fileInitialPreview            |         | array with html of the preview images
$fileUploadAsync               | false   | upload file asynchronously
$fileBrowseClear               | false   | clear file when browsing for new file
$fileDefaultBrowseEvent        | true    | set false and created a custom .on("filebrowse") event
$fileDefaultBatchSelectedEvent | true    | set false and created a custom .on("filebatchselected") event
$fileDefaultUploadSuccessEvent | true    | set false and created a custom .on("filebatchuploadsuccess") event
$fileDefaultUploadErrorEvent   | true    | set false and created a custom .on("fileuploaderror") event
-----------------------------------------------------------------------------------
*}
<script>
    $('{$fileID}').fileinput({
        {if isset($fileUploadUrl)}
        uploadUrl: '{$fileUploadUrl}',
        {/if}
        {if isset($fileDeleteUrl)}
        deleteUrl: '{$fileDeleteUrl}',
        {/if}
        autoOrientImage: false,
        showUpload: false,
        showRemove: false,
        showCancel: false,
        uploadAsync: {$fileUploadAsync|default:'false'},
        showPreview: {$filePreview|default:'false'},
        initialPreviewShowDelete: false,
        fileActionSettings: {
            showZoom: false,
            showRemove: false,
            showDrag: false
        },
        {if isset($fileExtraData)}
        uploadExtraData: {$fileExtraData},
        {/if}
        allowedFileExtensions :
            {if empty($fileAllowedExtensions)}
                ['jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'svg']
            {else}
                {$fileAllowedExtensions}
            {/if},
        overwriteInitial: true,
        {if $fileIsSingle|default:true}
        initialPreviewCount: 1,
        {/if}
        theme: 'fas',
        language: '{$language|mb_substr:0:2}',
        browseOnZoneClick: true,
        maxFileSize: {$fileMaxSize|default:500},
        {if $fileIsSingle|default:true}
        maxFilesNum: 1,
        {/if}
        {if $filePreview|default:false}
        initialPreviewConfig: {if isset($fileInitialPreviewConfig)}{$fileInitialPreviewConfig}{else}[]{/if},
        initialPreview: {if isset($fileInitialPreview)}{$fileInitialPreview}{else}[]{/if},
        {/if}
    });

    {if $fileDefaultBrowseEvent|default:true}
        $('{$fileID}').on("filebrowse", function(event, files) {
            {if $fileBrowseClear|default:false}
                $('{$fileID}').fileinput('clear');
            {/if}
            $('{$fileID}-upload-success').hide().addClass('hidden');
            $('{$fileID}-upload-error').hide().addClass('hidden');
        });
    {/if}
    {if $fileDefaultBatchSelectedEvent|default:true}
        $('{$fileID}').on("filebatchselected", function(event, files) {
            $('{$fileID}').fileinput("upload");
        });
    {/if}
    {if $fileDefaultUploadSuccessEvent|default:true}
        $('{$fileID}').on('filebatchuploadsuccess', function(event, data) {
            if (data.response.status === 'OK') {
                $('{$fileID}-upload-success').show().removeClass('hidden');
            } else {
                $('{$fileID}-upload-error').show().removeClass('hidden');
            }
        });
    {/if}
    {if $fileDefaultUploadErrorEvent|default:true}
        $('{$fileID}').on('fileuploaderror', function(event, data, msg) {
            $('{$fileID}-upload-error').show().removeClass('hidden');
            $('{$fileID}-upload-error').append('<p style="margin-top:20px">'+msg+'</p>')
        });
    {/if}
</script>