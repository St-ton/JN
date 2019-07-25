<div class="backend-search">
    <span class="fal fa-search fa-fw" data-fa-transform="grow-4"></span>
    <input id="backend-search-input" placeholder="{__('searchTerm')}" name="cSuche" type="search"
           value="" autocomplete="off">
    <div class="dropdown-menu" id="dropdown-search"></div>
    <script>
        var lastIoSearchCall    = null;
        var searchItems         = null;
        var selectedSearchIndex = null;
        var selectedSearchItem  = null;
        var searchDropdown      = $('#dropdown-search');
        var searchInput         = $('#backend-search-input');
        var lastSearchTerm      = '';

        searchInput
            .on('input', function() {
                lastSearchTerm = $(this).val();

                if (lastSearchTerm.length >= 3) {
                    if(lastIoSearchCall) {
                        lastIoSearchCall.abort();
                        lastIoSearchCall = null;
                    }

                    lastIoSearchCall = ioCall('adminSearch', [lastSearchTerm], function (html) {
                        if (html) {
                            searchDropdown.html(html).addClass('show');
                        } else {
                            searchDropdown.removeClass('show');
                        }

                        searchItems         = null;
                        selectedSearchIndex = null;
                        selectedSearchItem  = null;
                    });
                } else {
                    searchDropdown.html('');
                    searchDropdown.removeClass('show');
                    searchItems         = null;
                    selectedSearchIndex = null;
                    selectedSearchItem  = null;
                }
            })
            .on('keydown', function(e) {
                if(e.key === 'Enter') {
                    if(selectedSearchItem === null) {
                        var searchString = $('#backend-search-input').val();
                        if (searchString.length >= 3) {
                            window.location.href = 'searchresults.php?cSuche=' + searchString;
                        }
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
                searchDropdown.removeClass('show');
            }
        });

        function arrowNavigate(down = false)
        {
            if(searchItems === null) {
                searchItems = searchDropdown.find('.backend-search-item');
            }

            if (searchItems.length > 0) {
                if(selectedSearchIndex === null) {
                    if(down) {
                        selectedSearchIndex = 0;
                    } else {
                        selectedSearchIndex = searchItems.length - 1;
                    }
                } else {
                    if(down) {
                        selectedSearchIndex = (selectedSearchIndex + 1) % searchItems.length;
                    } else {
                        selectedSearchIndex = (selectedSearchIndex - 1 + searchItems.length) % searchItems.length;
                        if(selectedSearchIndex === searchItems.length - 1) {
                            selectedSearchIndex = null;
                        }
                    }
                }

                searchDropdown.find('.selected').removeClass('selected');

                if (selectedSearchIndex === null) {
                    selectedSearchItem = null;
                    searchInput.val(lastSearchTerm);
                } else {
                    selectedSearchItem = $(searchItems[selectedSearchIndex]);
                    selectedSearchItem.addClass('selected');
                    selectedSearchItem.find('a').focus();
                    var mark = selectedSearchItem.find('mark');
                    if (mark.length > 0) {
                        searchInput.val(mark[0].innerText);
                    }
                }

                searchInput.focus();
            }
        }
    </script>
</div>