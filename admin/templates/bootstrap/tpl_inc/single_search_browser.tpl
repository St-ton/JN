{*
     @deprecated since 4.06 the functionality of this component can simply be covered with a twitter typeahead. See
        the function enableTypeahead() in global.js to turn a text input into a suggestion input.
*}
<div class="single_search_browser">
    <fieldset>
        <div class="input-group">
            <span class="input-group-addon">
                {__('searchThrough')}
                <img id="loaderimg" src="templates/bootstrap/gfx/widgets/ajax-loader.gif">
            </span>
            <input type="text" value="" autocomplete="off" class="form-control" />
        </div>
        <div class="search">
            <select size="15" class="ssb-search"></select>
        </div>
        <div class="text-center">
            <div class="btn-group">
                <a href="#" class="btn btn-primary button add">{__('save')}</a>
                <a href="#" class="btn btn-danger button remove"><i class="fa fa-exclamation"></i> {__('cancel')}</a>
            </div>
        </div>
    </fieldset>
</div>
