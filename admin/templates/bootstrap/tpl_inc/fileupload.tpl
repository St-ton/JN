{*
-----------------------------------------------------------------------------------
variable name                  | default | description
-----------------------------------------------------------------------------------
$fileID                        |         | input id
$fileName                      |         | input name
$fileRequired                  |         | input required
$fileClass                     |         | input class
$fileAllowedExtensions         | ----->  | default: ['jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'svg']
$fileUploadUrl                 | false   | url to upload file via ajax
$fileDeleteUrl                 |         | url to delete file via ajax
$filePreview                   | true    | enable previe image
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
{$fileIDFull   = '#'|cat:$fileID}
{$fileIsSingle = $fileIsSingle|default:true}
<input class="custom-file-input {$fileClass|default:''}"
       type="file"
       name="{if isset($fileName)}{$fileName}{else}{$fileID}{/if}"
       id="{$fileID}"
       tabindex="1"
       {if $fileRequired|default:false}required{/if}
       {if !$fileIsSingle}multiple{/if}/>

{if $fileSuccessMsg|default:false}
    <div id="{$fileID}-upload-success" class="alert alert-success d-none mt-3">
        {$fileSuccessMsg}
    </div>
{/if}
{if $fileErrorMsg|default:false}
    <div id="{$fileID}-upload-error" class="alert alert-danger d-none mt-3"></div>
{/if}

<script>
    (function () {
        let $file = $('{$fileIDFull}'),
            $fileSuccess = $('{$fileIDFull}-upload-success'),
            $fileError = $('{$fileIDFull}-upload-error');

        $file.fileinput({
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
            showPreview: {$filePreview|default:'true'},
            initialPreviewShowDelete: false,
            fileActionSettings: {
                showZoom: false,
                showRemove: false,
                showDrag: false
            },
            {if isset($fileExtraData)}
            uploadExtraData: {$fileExtraData},
            {/if}
            allowedFileExtensions:
            {if empty($fileAllowedExtensions)}
                ['jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'svg']
            {else}
            {$fileAllowedExtensions}
            {/if},
            overwriteInitial: {$fileOverwriteInitial|default:'true'},
            {if $fileIsSingle}
            initialPreviewCount: 1,
            {/if}
            theme: 'fas',
            language: '{$language|mb_substr:0:2}',
            browseOnZoneClick: true,
            {if $fileMaxSize|default:true !== 'false'}
            maxFileSize: {$fileMaxSize|default:6000},
            {/if}
            {if $fileIsSingle}
            maxFilesNum: 1,
            {/if}
            {if $filePreview|default:false}
            initialPreviewConfig: {if isset($fileInitialPreviewConfig)}{$fileInitialPreviewConfig}{else}[]{/if},
            initialPreview: {if isset($fileInitialPreview)}{$fileInitialPreview}{else}[]{/if},
            {/if}
        });

        {if $fileDefaultBrowseEvent|default:true}
        $file.on("filebrowse", function (event, files) {
            {if $fileBrowseClear|default:false}
            $file.fileinput('clear');
            {/if}
            $fileSuccess.addClass('d-none');
            $fileError.html('').addClass('d-none');
        });
        {/if}
        {if $fileDefaultBatchSelectedEvent|default:true}
        $file.on("filebatchselected", function (event, files) {
            if ($file.fileinput('getFilesCount') > 0) {
                $file.fileinput("upload");
            }
        });
        {/if}
        {if $fileDefaultUploadSuccessEvent|default:true}
        $file.on('filebatchuploadsuccess', function (event, data) {
            if (data.response.status === 'OK') {
                $fileSuccess.removeClass('d-none');
            } else {
                $fileError.removeClass('d-none');
            }
        });
        {/if}
        {if $fileDefaultUploadErrorEvent|default:true}
        $file.on('fileuploaderror, fileerror', function (event, data, msg) {
            $fileError.removeClass('d-none');
            $fileError.append('<p style="margin-top:20px">' + msg + '</p>')
        });
        {/if}
    }());
</script>