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
        uploadAsync: false,
        showPreview: {$filePreview|default:false},
        fileActionSettings: {
            showZoom: false,
            showRemove: false,
            showDrag: false
        },
        allowedFileExtensions : ['jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'svg'],
        overwriteInitial: true,
        initialPreviewCount: 1,
        theme: 'fas',
        language: '{$language|mb_substr:0:2}',
        browseOnZoneClick: true,
        maxFileSize: {$fileMaxSize|default:500},
        {if $fileIsSingle|default:true}
        maxFilesNum: 1,
        {/if}
        {if $filePreview|default:false}
        initialPreviewConfig: {$fileInitialPreviewConfig},
        initialPreview: {$fileInitialPreview}
        {/if}
    }).on("filebrowse", function(event, files) {
        $('{$fileID}').fileinput('clear');
        $('{$fileID}-upload-success').hide().addClass('hidden');
        $('{$fileID}-upload-error').hide().addClass('hidden');
        console.log('filebrowse');
    }).on("filebatchselected", function(event, files) {
        console.log('filebatchselected');
        $('{$fileID}').fileinput("upload");
    }).on('filebatchuploadsuccess', function(event, data) {
        console.log('filebatchuploadsuccess');
        if (data.response.status === 'OK') {
            $('{$fileID}-upload-success').show().removeClass('hidden');
        } else {
            $('{$fileID}-upload-error').show().removeClass('hidden');
        }
    }).on('fileuploaderror', function(event, data, msg) {
        console.log('fileuploaderror');
        $('{$fileID}-upload-error').show().removeClass('hidden');
        $('{$fileID}-upload-error').append('<p style="margin-top:20px">'+msg+'</p>')
    });
</script>