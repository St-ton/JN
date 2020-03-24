{*
-----------------------------------------------------------------------------------
variable name                  | default | description
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
$fileShowUpload                | false   | show upload button
$fileShowRemove                | false   | show remove button
$fileShowCancel                | false   | show cancel button
$fileOverwriteInitial          | true    | override initial file
$fileDefaultBrowseEvent        | true    | set false and created a custom .on("filebrowse") event
$fileDefaultBatchSelectedEvent | true    | set false and created a custom .on("filebatchselected") event
$fileDefaultUploadSuccessEvent | true    | set false and created a custom .on("filebatchuploadsuccess") event
$fileDefaultUploadErrorEvent   | true    | set false and created a custom .on("fileuploaderror") event
$fileSuccessMsg                | false   | success message after upload
$fileErrorMsg                  | false   | error message while uploading - automatically generated
-----------------------------------------------------------------------------------
*}
{$fileIDNoHashtag = substr($fileID,1)}
<script>
    $('{$fileID}').fileinput({
        {if isset($fileUploadUrl)}
        uploadUrl: '{$fileUploadUrl}',
        {/if}
        {if isset($fileDeleteUrl)}
        deleteUrl: '{$fileDeleteUrl}',
        {/if}
        autoOrientImage: false,
        showUpload: {$fileShowUpload|default:'false'},
        showRemove: {$fileShowRemove|default:'false'},
        showCancel: {$fileShowCancel|default:'false'},
        cancelClass: 'btn btn-outline-primary',
        uploadClass: 'btn btn-outline-primary',
        removeClass: 'btn btn-outline-primary',
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
        overwriteInitial: {$fileOverwriteInitial|default:'true'},
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
            $('{$fileID}-upload-error').html('').hide().addClass('hidden');
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
            $('{$fileID}-upload-error').append('<p style="margin-top:20px">' + msg + '</p>')
        });
    {/if}
</script>

{if $fileSuccessMsg|default:false}
    <div id="{$fileIDNoHashtag}-upload-success" class="alert alert-success hidden mt-3">
        {$fileSuccessMsg}
    </div>
{/if}
{if $fileErrorMsg|default:false}
    <div id="{$fileIDNoHashtag}-upload-error" class="alert alert-danger hidden mt-3">
        {$fileErrorMsg|default:''}
    </div>
{/if}
