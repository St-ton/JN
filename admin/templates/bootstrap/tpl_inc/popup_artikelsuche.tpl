<h1>{__('labelSearchProduct')}</h1>
<fieldset>
    <input class="form-control" type="text" id="article_list_input" value="{if isset($cSearch)}{$cSearch}{/if}" autocomplete="off" />
    <div class="select_wrapper">
        <div class="search">
            <h2>{__('found')} {__('product')}</h2>
            <select multiple="multiple" name="article_list_found">
            </select>
        </div>
        <div class="added">
            <h2>{__('selected')} {__('product')}</h2>
            <select multiple="multiple" name="article_list_selected">
            </select>
        </div>
        <div class="clear"></div>
    </div>
    <div class="tcenter btn-group">
        <a href="#" class="btn btn-default" id="article_list_add"><i class="fa fa-check-square-o"></i> {__('add')}</a>
        <a href="#" class="btn btn-default" id="article_list_remove"><i class="fa fa-square-o"></i> {__('delete')}</a>
        <a href="#" class="btn btn-primary" id="article_list_save"><i class="fa fa-save"></i> {__('save')}</a>
        <a href="#" class="btn btn-danger" id="article_list_cancel"><i class="fa fa-exclamation"></i> {__('cancel')}</a>
    </div>
</fieldset>