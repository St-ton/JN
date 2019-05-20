<h2>{__('labelSearchCategory')}</h2>
<fieldset>
    <input class="form-control" type="text" id="categories_list_input" value="{if isset($cSearch)}{$cSearch}{/if}" autocomplete="off" />
    <div class="select_wrapper">
        <div class="search">
            <h2>{__('found')} {__('categories')}</h2>
            <select multiple="multiple" name="categories_list_found">
            </select>
        </div>
        <div class="added">
            <h2>{__('selected')} {__('categories')}</h2>
            <select multiple="multiple" name="categories_list_selected">
            </select>
        </div>
        <div class="clear"></div>
    </div>
    <div class="tcenter btn-group">
        <a href="#" class="btn btn-primary" id="categories_list_save"><i class="fa fa-save"></i> {__('save')}</a>
        <a href="#" class="btn btn-danger" id="categories_list_cancel"><i class="fa fa-warning"></i> {__('cancel')}</a>
    </div>
</fieldset>