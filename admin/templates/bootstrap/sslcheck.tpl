{config_load file="$lang.conf" section="sslcheck"}
{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=#sslcheck# cBeschreibung=#sslcheckDesc# cDokuURL=#sslcheckURL#}
<div id="content" class="container-fluid">
    <div class="container-fluid2">
        <a href="#" data-callback="check">Check</a>
        <div id="result">
            {include file='tpl_inc/sslcheck.tpl'}
        </div>
    </div>
</div>

<script>
    var adminPath = '{$PFAD_ADMIN}';
    {literal}

    function check($element)
    {
        var url = $element.attr('href');

        ioManagedCall(adminPath, 'getSSLCheck', [], function(result, error) {
            var res = result.data;
            console.log(res.data);
            $('#result').html(res.tpl);
        });
    }

    /**
     * @deprecated since 4.06
     * @param url
     * @param params
     * @param callback
     */
    function ajaxManagedCall(url, params, callback)
    {
        ajaxCall(url, params, function(result, xhr) {
            if (xhr && xhr.error && xhr.error.code == 401) {
                createNotify({
                    title: 'Sitzung abgelaufen',
                    message: 'Sie werden zur Anmelde-Maske weitergeleitet...',
                    icon: 'fa fa-lock'
                }, {
                    type: 'danger',
                    onClose: function() {
                        window.location.pathname = '/' + adminPath + 'index.php';
                    }
                });
            }
            else if (typeof callback === 'function') {
                callback(result, result.error);
            }
        });
    }

    function init_bindings()
    {
        $('[data-callback]').click(function(e) {
            e.preventDefault();
            var $element = $(this);
            if ($element.attr('disabled') !== undefined) {
                return false;
            }
            var callback = $element.data('callback');
            if (!$(e.target).attr('disabled')) {
                window[callback]($element);
            }
        });
    }

    $(function() {
        init_bindings();
    });

    {/literal}
</script>

{include file='tpl_inc/footer.tpl'}