<div class="backend-search">
    <i class="fa fa-search"></i>
    <input id="backend-search-input" placeholder="Suchbegriff" name="cSuche" type="search"
           value="" autocomplete="off">
    <ul id="backend-search-dropdown"></ul>
    <script>
        var lastIoSearchCall = null;
        var searchDropdown = $('#backend-search-dropdown');

        $('#backend-search-input')
            .on('input', function() {
                var value = $(this).val();

                if (value.length >= 3) {
                    if(lastIoSearchCall) {
                        lastIoSearchCall.abort();
                        lastIoSearchCall = null;
                    }

                    lastIoSearchCall = ioCall('adminSearch', [value], function (data) {
                        var tpl = data.data.tpl;

                        if (tpl) {
                            searchDropdown.html(tpl).addClass('open');
                        } else {
                            searchDropdown.removeClass('open');
                        }
                    });
                } else {
                    searchDropdown.removeClass('open');
                }
            })
            .keydown(function(e) {
                if(e.key === 'Enter') {
                    var searchString = $('#backend-search-input').val();

                    window.location.href = 'einstellungen.php?cSuche=' + searchString
                        + '&einstellungen_suchen=1';
                } else if(e.key === 'ArrowDown') {
                    console.log("down")
                }
            });
        $(document).click(function(e) {
            if ($(e.target).closest('.backend-search').length === 0) {
                searchDropdown.removeClass('open');
            }
        });
    </script>
</div>