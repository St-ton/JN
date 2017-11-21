<ul role="tablist" class="nav nav-tabs">
    <li class="active" role="presentation">
        <a aria-expanded="true" aria-controls="general" data-toggle="tab" role="tab" id="general-tab" href="#general">
            General
        </a>
    </li>
    <li role="presentation">
        <a data-toggle="tab"  aria-controls="searchpicker-articlePicker" data-toggle="tab" role="tab" href="#searchpicker-articlePicker">Artikel</a>
    </li>
    <li role="presentation">
        <a aria-controls="style-design" data-toggle="tab" id="style-design-tab" role="tab" href="#style-design" aria-expanded="false">
            Style
        </a>
    </li>
</ul>

<div class="tab-content">
    <div id="general" class="tab-pane fade active in" role="tabpanel" aria-labelledby="general-tab">
        <div class="row">
            <div class="col-sm-6">
                <label for="list-style">Artikeldarstellung</label>
                <div class="radio" id="list-style">
                    <label class="radio-inline">
                        <input type="radio" name="listStyle" value="gallery"{if $properties['listStyle'] === 'gallery'} checked="checked"{/if}> Galerie
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="listStyle" value="list"{if $properties['listStyle'] === 'list'} checked="checked"{/if}> Liste
                    </label>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="product-slider-title">Class</label>
                    <input type="text" id="product-slider-title" name="attr[class]" class="form-control" value="{$properties.attr['class']}">
                </div>
            </div>
        </div>
    </div>
    {include file='./settings.tabcontent.searchpicker.tpl' searchPickerName='articlePicker'}
    {include file='./settings.tabcontent.style.tpl'}
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

    cmsLiveEditor.configSaveCallback = function ()
    {
        console.log(articlePicker.getSelection());
        $('#articleIds').val(articlePicker.getSelection().join(','));
    };
</script>