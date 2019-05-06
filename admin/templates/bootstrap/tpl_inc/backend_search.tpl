<div class="backend-search">
    <i class="fa fa-search"></i>
    <input id="backend-search-input" placeholder="Suchbegriff" name="cSuche" type="search"
           value="" autocomplete="off">
    <ul id="backend-search-dropdown"></ul>
    <script>
        var lastIoSearchCall    = null;
        var searchItems         = null;
        var selectedSearchIndex = null;
        var selectedSearchItem  = null;
        var searchDropdown      = $('#backend-search-dropdown');
        var searchInput         = $('#backend-search-input');

        searchInput
            .on('input', function() {
                var value = $(this).val();

                if (value.length >= 3) {
                    if(lastIoSearchCall) {
                        lastIoSearchCall.abort();
                        lastIoSearchCall = null;
                    }

                    lastIoSearchCall = ioCall('adminSearch', [value], function (html) {
                        if (html) {
                            searchDropdown.html(html).addClass('open');
                        } else {
                            searchDropdown.removeClass('open');
                        }

                        searchItems         = null;
                        selectedSearchIndex = null;
                        selectedSearchItem  = null;
                    });
                } else {
                    searchDropdown.removeClass('open');
                }
            })
            .on('keydown', function(e) {
                if(e.key === 'Enter') {
                    if(selectedSearchItem === null) {
                        var searchString = $('#backend-search-input').val();
                        // window.location.href = 'einstellungen.php?cSuche=' + searchString + '&einstellungen_suchen=1';
                        window.location.href = 'searchresults.php?cSuche=' + searchString + '&einstellungen_suchen=1';
                    } else {
                        window.location.href = selectedSearchItem.find('a').attr('href');
                    }
                } else if(e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                    arrowNavigate(e.key === 'ArrowDown');
                    e.preventDefault();
                }
            });
        searchDropdown.on('keydown', function (e) {
            if(e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                arrowNavigate(e.key === 'ArrowDown');
                e.preventDefault();
            }
        });
        $(document).on('click', function(e) {
            if ($(e.target).closest('.backend-search').length === 0) {
                searchDropdown.removeClass('open');
            }
        });

        function arrowNavigate(down = false)
        {
            if(searchItems === null) {
                searchItems = searchDropdown.find('.backend-search-item');
            }

            if(selectedSearchIndex === null) {
                if(down)
                    selectedSearchIndex = 0;
                else
                    selectedSearchIndex = searchItems.length - 1;
            } else {
                if(down)
                    selectedSearchIndex = (selectedSearchIndex + 1) % searchItems.length;
                else
                    selectedSearchIndex = (selectedSearchIndex - 1 + searchItems.length) % searchItems.length;
            }

            searchDropdown.find('.selected').removeClass('selected');
            selectedSearchItem = $(searchItems[selectedSearchIndex]);
            selectedSearchItem.addClass('selected');
            selectedSearchItem.find('a').focus();
            searchInput.focus();
        }
    </script>
</div>