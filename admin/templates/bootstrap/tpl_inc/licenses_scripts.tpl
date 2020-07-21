<script type="text/javascript">
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
            if (result.id && result.html) {
                let itemID = '#' + result.id;
                if (result.notification) {
                    $('#updates-drop').html(result.notification);
                }
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
    const bindCallback = function (btn, e) {
        console.log('bindCallback()');
        btn.attr('disabled', true);
        btn.find('i').addClass('fa-spin');
        $.ajax({
            method: 'POST',
            url: '{$shopURL}/admin/licenses.php',
            data: $(e.target).serialize()
        }).done(function (r) {
            const result = JSON.parse(r);
            if (result.replaceWith) {
                for (let itemID in result.replaceWith) {
                    $(itemID).replaceWith(result.replaceWith[itemID]);
                }
            }
            btn.attr('disabled', false);
            btn.find('i').removeClass('fa-spin');
        });
        return false;
    };
    var formCount = 0,
        done = 0;
    $(document).ready(function () {
        $('#content_wrapper').on('submit', '#bound-licenses .update-item-form', function (e) {
            return dlCallback($(e.target).find('.update-item'), e);
        });
        $('#content_wrapper').on('submit', '#bound-licenses .install-item-form', function (e) {
            return dlCallback($(e.target).find('.install-item'), e);
        });
        $('#content_wrapper').on('click', '#bound-licenses #update-all', function (e) {
            hideUpdateAll();
            done = 0;
            const forms = $('#bound-licenses .update-item-form');
            formCount = forms.length;
            forms.submit();
        });
        $('#content_wrapper').on('click', '#bound-licenses #install-all', function (e) {
            hideInstallAll();
            done = 0;
            const forms = $('#bound-licenses .install-item-form');
            formCount = forms.length;
            forms.submit();
        });
        $('#content_wrapper').on('submit', '#unbound-licenses .set-binding-form', function (e) {
            return bindCallback($(e.target).find('.set-binding'), e);
        });
        $('#content_wrapper').on('submit', '.clear-binding-form', function (e) {
            return bindCallback($(e.target).find('.clear-binding'), e);
        });
    });
</script>
