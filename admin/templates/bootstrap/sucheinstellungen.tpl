{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/einstellungen_bearbeiten.tpl'}
{if $createIndex !== false}
    <script type="text/javascript">
        var createIndex = '{$createIndex}';
        var createCount = 0;
    </script>
    <script type="text/javascript">
        {literal}
        function showIndexNotification(pResult) {
            var type = 'info';
            var msg  = '';

            if (pResult && pResult.error && pResult.error.length) {
                type = 'danger';
                msg  = pResult.error;
            } else if (pResult && pResult.hinweis) {
                msg  = pResult.hinweis;
                createCount++;
            } else {
                return null;
            }

            createNotify({
                title: 'Volltextsuche verwenden',
                message: msg
            }, {
                type: type
            });

            if (createCount >= 2) {
                $('.alert.alert-danger').hide(300);
                updateNotifyDrop();
            }
        }

        ajaxCall('sucheinstellungen.php', {action: 'createIndex', create: createIndex, index: 'tartikel'}, function (result, xhr) {
            showIndexNotification(result);
        });
        ajaxCall('sucheinstellungen.php', {action: 'createIndex', create: createIndex, index: 'tartikelsprache'}, function (result, xhr) {
            showIndexNotification(result);
        });
        {/literal}
    </script>
{/if}
{include file='tpl_inc/footer.tpl'}