<ul role="tablist" class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#searchpicker-articlePicker">Artikel</a></li>
</ul>

<div class="tab-content">
    {include file='./settings.tabcontent.searchpicker.tpl' searchPickerName='articlePicker'}
</div>

<input type="hidden" name="articleIds" value="{$properties.articleIds}" id="articleIds">

<script>
    $(function () {
        articleIds = [{$properties.articleIds}].map(function(x){ return x.toString();});

        articlePicker = new SearchPicker({
            searchPickerName:  'articlePicker',
            getDataIoFuncName: 'getProducts',
            keyName:           'kArtikel',
            renderItemCb:      function (item) {
                return '<p class="list-group-item-text">' + item.cName + ' <em>(' + item.cArtNr + ')</em></p>';
            },
            selectedKeysInit:  articleIds
        });
    });

    jleHost.configSaveCallback = function ()
    {
        console.log(articlePicker.getSelection());
        $('#articleIds').val(articlePicker.getSelection().join(','));
    };
</script>