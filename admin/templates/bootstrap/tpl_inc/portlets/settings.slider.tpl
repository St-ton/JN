
{include file='tpl_inc/searchpicker_modal.tpl'
    searchPickerName='articlePicker'
    modalTitle='Artikel ausw&auml;hlen'
    searchInputLabel='Suche nach Artikelnamen'
}

<input type="hidden" name="articleIds" value="">

<script>
    $(function () {
        articlePicker = new SearchPicker({
            searchPickerName:  'articlePicker',
            getDataIoFuncName: 'getProducts',
            keyName:           'cArtNr',
            renderItemCb:      function (item) {
                return '<p class="list-group-item-text">' + item.cName + ' <em>(' + item.cArtNr + ')</em></p>';
            },
            onApply:           onApplySelectedArticles,
            selectedKeysInit:  [{$properties.articleIds|implode:','}]
        });
        onApplySelectedArticles(articlePicker.getSelection());
        $('#articlePicker-modal').modal('show')
    });
    function onApplySelectedArticles(selectedArticles)
    {
        if (selectedArticles.length > 0) {
//            $('#articleSelectionInfo').val(selectedArticles.length + ' Artikel');
//            $('#cArtikel').val(selectedArticles.join(';') + ';');
        } else {
//            $('#articleSelectionInfo').val('Alle Artikel');
//            $('#cArtikel').val('');
        }
    }
</script>