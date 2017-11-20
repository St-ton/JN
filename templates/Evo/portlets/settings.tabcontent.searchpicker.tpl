{*
    Parameters:
        searchPickerName - page unique id for the search picker instance (e.g. 'customer', 'product')
*}
<div id="searchpicker-articlePicker" class="tab-pane fade" role="tabpanel">
    <div class="input-group">
        <label for="{$searchPickerName}-search-input" class="sr-only">Suche:</label>
        <input type="text" class="form-control" id="{$searchPickerName}-search-input" placeholder="Suche"
               autocomplete="off">
        <span class="input-group-btn">
            <button type="button" class="btn btn-default" id="{$searchPickerName}-reset-btn"
                    title="Eingabe l&ouml;schen">
                <i class="fa fa-eraser"></i>
            </button>
        </span>
    </div>
    <h5 id="{$searchPickerName}-list-title"></h5>
    <div class="list-group" id="{$searchPickerName}-result-list" style="max-height:500px;overflow:auto;">
    </div>
    <div class="btn-group">
        <button type="button" class="btn btn-xs btn-primary" id="{$searchPickerName}-select-all-btn">
            <i class="fa fa-check-square-o"></i>
            {#searchpickerSelectAllShown#}
        </button>
        <button type="button" class="btn btn-xs btn-danger" id="{$searchPickerName}-unselect-all-btn">
            <i class="fa fa-square-o"></i>
            {#searchpickerUnselectAllShown#}
        </button>
    </div>
</div>