<script type="text/javascript">
    $(document).ready(function () {
        const showUpdateAll = function () {
            const btn = $('#update-all');
            btn.attr('disabled', false);
            btn.find('i').removeClass('fa-spin');
        };
        const hideUpdateAll = function () {
            const btn = $('#update-all');
            btn.attr('disabled', true);
            btn.find('i').addClass('fa-spin');
        };
        const showInstallAll = function () {
            const btn = $('#install-all');
            btn.attr('disabled', false);
            btn.find('i').removeClass('fa-spin');
        };
        const hideInstallAll = function () {
            const btn = $('#install-all');
            btn.attr('disabled', true);
            btn.find('i').addClass('fa-spin');
        };
        const dlCallback = function (btn, e) {
            btn.attr('disabled', true);
            btn.find('i').addClass('fa-spin');
            $.ajax({
                method: 'POST',
                url: '{$shopURL}/admin/licenses.php',
                data: $(e.target).serialize()
            }).done(function (r) {
                const result = JSON.parse(r);
                console.log('RES: ', result);
                if (result.id && result.html) {
                    let itemID = '#' + result.id;
                    if (result.action === 'update' || result.action === 'install') {
                        itemID = '#license-item-' + result.id;
                    }
                    $(itemID).replaceWith(result.html);
                    btn.attr('disabled', false);
                    btn.find('i').removeClass('fa-spin');
                }
                ++done;
                if (formCount > 0 && formCount === done) {
                    showUpdateAll();
                    showInstallAll();
                }
            });
            return false;
        };
        var formCount = 0,
            done = 0;
        $('#active-licenses').on('submit', '.update-item-form', function (e) {
            return dlCallback($(e.target).find('.update-item'), e);
        });
        $('#active-licenses').on('submit', '.install-item-form', function (e) {
            return dlCallback($(e.target).find('.install-item'), e);
        });
        $('#active-licenses').on('click', '#update-all', function (e) {
            hideUpdateAll();
            done = 0;
            const forms = $('#active-licenses .update-item-form');
            formCount = forms.length;
            forms.submit();
        });
        $('#active-licenses').on('click', '#install-all', function (e) {
            hideInstallAll();
            done = 0;
            const forms = $('#active-licenses .install-item-form');
            formCount = forms.length;
            forms.submit();
        });
    });
</script>
