{*
    Parameters:
        itemName - page unique id for the kind of items to be searched for (e.g. 'customer', 'product')
        modalTitle - the modal dialogs title
        searchInputLabel - the caption for the search input field
        getDataIoFunc - the Ajax function name that fetches the items to be searched for
        renderItemCb - js callback function name that gets an item object and returns the content to be rendered
            for its list item
        onApply - js callback function name that gets called on apply selection click with the current selected keys
            array
        selectedKeys - array of the items keys that are initially selected
*}
<script>
    var itemSearcher_{$itemName} = new (function ()
    {
        var self               = this;
        var searchString       = '';
        var lastSearchString   = '';
        var selectedKeys       = [{','|implode:$selectedKeys}];
        var backupSelectedKeys = [];
        var foundItems         = [];
        var dataIoFuncName     = '{$getDataIoFunc}';
        var getRenderedItem    = null;
        var onApply            = null;
        var $searchModal       = null;
        var $searchResultList  = null;
        var $listTitle         = null;
        var $searchInput       = null;
        var $applyButton       = null;
        var $cancelButton      = null;
        var $resetButton       = null;
        var $selectAllButton   = null;
        var $unselectAllButton = null;

        $(function () {
            getRenderedItem    = {$renderItemCb};
            onApply            = {$onApply};
            $searchModal       = $('#{$itemName}-search-modal');
            $searchResultList  = $('#{$itemName}-search-result-list');
            $listTitle         = $('#{$itemName}-list-title');
            $searchInput       = $('#{$itemName}-search-input');
            $applyButton       = $('#{$itemName}-apply-btn');
            $cancelButton      = $('#{$itemName}-cancel-btn');
            $resetButton       = $('#{$itemName}-reset-btn');
            $selectAllButton   = $('#{$itemName}-select-all-btn');
            $unselectAllButton = $('#{$itemName}-unselect-all-btn');

            $searchModal.on('show.bs.modal', self.onShow);
            $searchInput.keyup(self.onChangeSearchInput);
            $applyButton.click(self.onApply);
            $cancelButton.click(self.onCancel);
            $resetButton.click(self.onResetSearchInput);
            $selectAllButton.click(self.selectAllShownItems.bind(self, true));
            $unselectAllButton.click(self.selectAllShownItems.bind(self, false));
        });

        self.onShow = function ()
        {
            backupSelectedKeys = selectedKeys.slice();
            self.onResetSearchInput();
            self.updateItemList();
        };

        self.onApply = function ()
        {
            onApply(selectedKeys);
        };

        self.onCancel = function ()
        {
            selectedKeys = backupSelectedKeys.slice();
        };

        self.onResetSearchInput = function ()
        {
            $searchInput.val('');
            self.onChangeSearchInput();
        };

        self.onChangeSearchInput = function ()
        {
            searchString = $searchInput.val();

            if (searchString !== lastSearchString) {
                lastSearchString = searchString;
                self.updateItemList();
            }
        };

        self.selectAllShownItems = function (selected)
        {
            foundItems.forEach(function (item) {
                self.select(item.id, selected);
            });
        };

        self.updateItemList = function ()
        {
            if (searchString !== '') {
                ioGetJson(dataIoFuncName, [100, searchString], self.itemsReceived);
            } else if (selectedKeys.length > 0) {
                ioGetJson(dataIoFuncName, [0, selectedKeys], self.itemsReceived);
            } else {
                $searchResultList.empty();
                foundItems = [];
                self.updateListTitle();
            }
        };

        self.itemsReceived = function (items)
        {
            foundItems = items;
            self.updateListTitle();
            $searchResultList.empty();

            items.forEach(function (item) {
                item.id = parseInt(item.id);
                $('<a>')
                    .addClass('list-group-item' + (self.isSelected(item.id) ? ' active' : ''))
                    .attr('id', '{$itemName}-' + item.id)
                    .css('cursor', 'pointer')
                    .click(function () { self.select(item.id, !self.isSelected(item.id)); })
                    .html(getRenderedItem(item))
                    .appendTo($searchResultList);
            });
        };

        self.updateListTitle = function ()
        {
            if (searchString !== '') {
                $listTitle.html('Gefundene Eintr&auml;ge: ' + foundItems.length);
            } else if (selectedKeys.length > 0) {
                $listTitle.html('Alle ausgew&auml;hlten Eintr&auml;ge: ' + selectedKeys.length);
            } else {
                $listTitle.html('Bisher sind keine Eintr&auml;ge ausgew&auml;hlt. Nutzen Sie die Suche!');
            }
        };

        self.select = function (id, selected)
        {
            if (selected) {
                $('#{$itemName}-' + id).addClass('active');

                if(selectedKeys.indexOf(id) === -1) {
                    selectedKeys.push(id);
                }
            } else {
                $('#{$itemName}-' + id).removeClass('active');
                removeElementFromArray(selectedKeys, id);
            }

            self.updateListTitle();
        };

        self.isSelected = function (id)
        {
            return selectedKeys.indexOf(id) !== -1;
        };
    })();

    removeElementFromArray = window.removeElementFromArray || function (a, e)
    {
        var i = a.indexOf(e);
        if (i !== -1) {
            a.splice(i, 1);
        }
    };
</script>
<div class="modal fade" id="{$itemName}-search-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fa fa-times"></i>
                </button>
                <h4 class="modal-title">{$modalTitle}</h4>
            </div>
            <div class="modal-body">
                <div class="input-group">
                    <label for="{$itemName}-search-input" class="sr-only">
                        {$searchInputLabel}:
                    </label>
                    <input type="text" class="form-control" id="{$itemName}-search-input" placeholder="Suche"
                           autocomplete="off">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default" id="{$itemName}-reset-btn"
                                title="Eingabe l&ouml;schen">
                            <i class="fa fa-eraser"></i>
                        </button>
                    </span>
                </div>
                <h5 id="{$itemName}-list-title">Suchergebnisse</h5>
                <div class="list-group" id="{$itemName}-search-result-list" style="max-height:500px;overflow:auto;">
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-primary" id="{$itemName}-select-all-btn">
                        <i class="fa fa-check-square-o"></i>
                        Alle ausw&auml;hlen
                    </button>
                    <button type="button" class="btn btn-xs btn-danger" id="{$itemName}-unselect-all-btn">
                        <i class="fa fa-square-o"></i>
                        Alle abw&auml;hlen
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <div class="btn-group">
                    <button type="button" class="btn btn-danger" data-dismiss="modal" id="{$itemName}-cancel-btn">
                        <i class="fa fa-times"></i>
                        {#cancel#}
                    </button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" id="{$itemName}-apply-btn">
                        <i class="fa fa-save"></i>
                        {#apply#}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>