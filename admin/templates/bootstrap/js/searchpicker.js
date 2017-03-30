/**
 * SearchPicker constructer
 *
 * @param searchPickerName - page unique id for the kind of items to be searched for (e.g. 'customer', 'product')
 * @param getDataIoFuncName - the Ajax function name that fetches the items to be searched for
 * @param keyName - name of the property that denotes the key column of each item
 * @param renderItemCb - callback function that gets an item object and returns the html content for its list item
 * @param onApply - callback function that gets called on apply selection click with the current selected keys array
 * @param selectedKeysInit - array of the items keys that are initially selected
 * @constructor
 */
function SearchPicker(searchPickerName, getDataIoFuncName, keyName, renderItemCb, onApply, selectedKeysInit)
{
    var self               = this;
    var searchString       = '';
    var lastSearchString   = '';
    var selectedKeys       = selectedKeysInit.slice();
    var backupSelectedKeys = [];
    var foundItems         = [];
    var dataIoFuncName     = getDataIoFuncName;
    var getRenderedItem    = renderItemCb;
    var closeAction        = '';
    var pendingRequest     = null;
    var $searchModal       = $('#' + searchPickerName + '-modal');
    var $searchResultList  = $('#' + searchPickerName + '-result-list');
    var $listTitle         = $('#' + searchPickerName + '-list-title');
    var $searchInput       = $('#' + searchPickerName + '-search-input');
    var $applyButton       = $('#' + searchPickerName + '-apply-btn');
    var $cancelButton      = $('#' + searchPickerName + '-cancel-btn');
    var $resetButton       = $('#' + searchPickerName + '-reset-btn');
    var $selectAllButton   = $('#' + searchPickerName + '-select-all-btn');
    var $unselectAllButton = $('#' + searchPickerName + '-unselect-all-btn');

    $(function () {
        $searchModal.on('show.bs.modal', self.onShow);
        $searchModal.on('hide.bs.modal', self.onHide);
        $searchInput.keyup(self.onChangeSearchInput);
        $applyButton.click(self.onApply);
        $cancelButton.click(self.onCancel);
        $resetButton.click(self.onResetSearchInput);
        $selectAllButton.click(self.selectAllShownItems.bind(self, true));
        $unselectAllButton.click(self.selectAllShownItems.bind(self, false));
        self.init();
    });

    self.init = function () {
        self.onResetSearchInput();
        self.updateItemList();
    };

    self.onShow = function ()
    {
        console.log(searchPickerName, 'show');
        backupSelectedKeys = selectedKeys.slice();
    };

    self.onHide = function () {
        console.log(searchPickerName, 'hide');

        if (closeAction === 'apply') {
            onApply(selectedKeys);
            self.init();
        } else if (closeAction === 'cancel') {
            selectedKeys = backupSelectedKeys.slice();
            self.init();
        }

        closeAction = 'cancel';
    };

    self.onApply = function ()
    {
        console.log(searchPickerName, 'apply');
        closeAction = 'apply';
    };

    self.onCancel = function ()
    {
        console.log(searchPickerName, 'cancel');
        closeAction = 'cancel';
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
            self.select(item[keyName], selected);
        });
    };

    self.updateItemList = function ()
    {
        if (searchString !== '') {
            if (pendingRequest !== null) {
                pendingRequest.abort();
            }

            pendingRequest = ioGetJson(dataIoFuncName, [searchString, 100], self.itemsReceived);
        } else if (selectedKeys.length > 0) {
            if (pendingRequest !== null) {
                pendingRequest.abort();
            }

            pendingRequest = ioGetJson(dataIoFuncName, [selectedKeys, 100, keyName], self.itemsReceived);
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
            // item[keyName] = parseInt(item[keyName]);
            $('<a>')
                .addClass('list-group-item' + (self.isSelected(item[keyName]) ? ' active' : ''))
                .attr('id', searchPickerName + '-' + item[keyName])
                .css('cursor', 'pointer')
                .click(function () { self.select(item[keyName], !self.isSelected(item[keyName])); })
                .html(getRenderedItem(item))
                .appendTo($searchResultList);
        });

        pendingRequest = null;
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
        var index = selectedKeys.indexOf(id);

        if (selected) {
            $('#' + searchPickerName + '-' + id).addClass('active');

            if (index === -1) {
                selectedKeys.push(id);
            }
        } else {
            $('#' + searchPickerName + '-' + id).removeClass('active');

            if (index !== -1) {
                selectedKeys.splice(index, 1);
            }
        }

        self.updateListTitle();
    };

    self.isSelected = function (id)
    {
        return selectedKeys.indexOf(id) !== -1;
    };

    self.getSelection = function ()
    {
        return selectedKeys;
    }
}
