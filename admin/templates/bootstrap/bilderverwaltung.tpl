{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="bilderverwaltung"}

{include file='tpl_inc/seite_header.tpl' cTitel=#bilderverwaltung# cBeschreibung=#bilderverwaltungDesc# cDokuURL=#bilderverwaltungURL#}
<div id="content">
    {if isset($success)}
        <div class="alert alert-success"><i class="fa fa-info-circle"></i> {$success}</div>
    {/if}
    <div class="panel panel-default">
        <div class="table-responsive">
            <table class="list table" id="cache-items">
                <thead>
                <tr>
                    <th class="tleft">Typ</th>
                    <th class="text-center">Insgesamt</th>
                    <th class="text-center">Im Cache</th>
                    <th class="text-center">Fehlerhaft</th>
                    <th class="text-center" width="125">Gr&ouml;&szlig;e</th>
                    <th class="text-center" width="300">Aktionen</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$items item="item"}
                    <tr data-type="{$item->type}">
                        <td class="item-name">{$item->name}</td>
                        <td class="text-center">
                            <span class="item-total">
                              {$item->stats->total}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="item-generated">
                              {(($item->stats->generated[$SIZE_XS] + $item->stats->generated[$SIZE_SM] + $item->stats->generated[$SIZE_MD] + $item->stats->generated[$SIZE_LG]) / 4)|round:0}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="item-corrupted">
                              {$item->stats->corrupted}
                            </span>
                        </td>
                        <td class="text-center item-total-size">
                            <i class="fa fa-spinner fa-spin"></i>
                        </td>
                        <td class="text-center action-buttons">
                            <div class="btn-group btn-group-xs" role="group">
                                <a class="btn btn-default" href="#" data-callback="cleanup" data-type="{$item->type}"><i class="fa fa-trash-o"></i> Verwaiste l&ouml;schen</a>
                                <a class="btn btn-default" href="#" data-callback="flush" data-type="{$item->type}"><i class="fa fa-trash-o"></i> Cache leeren</a>
                                <a class="btn btn-default" href="#" data-callback="generate"><i class="fa fa-cog"></i> Generieren</a>
                            </div>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    {literal}
    $(function () {
        updateStats();
    });

    function updateStats() {
        $('#cache-items tbody > tr').each(function (i, item) {
            var type = $(item).data('type');
            ioCall('loadStats', [type], function (data) {
                var totalCached = 0;
                $('.item-total', item).text(data.total);
                $('.item-corrupted', item).text(data.corrupted);
                $('.item-total-size', item).text(formatSize(data.totalSize));

                $(['xs', 'sm', 'md', 'lg']).each(function (i, size) {
                    totalCached += data.generated[size];
                });
                $('.item-generated', item).text(Math.round(totalCached / 4, 0));
            });
        });
    }

    var lastResults = null,
        lastTick = null,
        running = false,
        notify = null

    function generate() {
        startGenerate('product');
    }

    function cleanup() {
        running = true;
        lastResults = [];
        lastTick = new Date();
        notify = showGenerateNotify('Bilder werden aufger&auml;umt', 'L&ouml;sche Bilder...');
        $('.action-buttons a').attr('disabled', true);
        doCleanup(0);
    }

    function stopCleanup() {
        running = false;
        $('.action-buttons a').attr('disabled', false);
    }

    function finishCleanup(result) {
        stopCleanup();

        notify.update({
            progress: 100,
            message: 'Insgesamt ' + result.deletedImages + ' Bilder gel&ouml;scht.',
            type: 'success',
            title: 'Bilder erfolgreich aufger&auml;umt'
        });
    }

    function doCleanup(index) {
        lastTick = new Date().getTime();
        ioCall('cleanupStorage', [index], function (result) {
            var items = result.deletes,
                deleted = result.deletedImages,
                total = result.total,
                offsetTick = new Date().getTime() - lastTick,
                perItem = Math.floor(offsetTick / result.checkedFiles),
                avg,
                remaining,
                eta,
                readable,
                percent;
            if (lastResults.length >= 10) {
                lastResults.splice(0, 1);
            }
            lastResults.push(perItem);
            avg = average(lastResults);
            remaining = total - result.checkedFilesTotal;
            eta = Math.max(0, Math.ceil(remaining * avg));
            readable = shortGermanHumanizer(eta);
            percent = Math.round(result.checkedFilesTotal / total * 100, 0);
            notify.update({
                message: '<div class="row">' +
                '<div class="col-sm-4"><strong>' + percent + '</strong>%</div>' +
                '<div class="col-sm-4 text-center">' + result.checkedFilesTotal + ' / ' + total + '</div>' +
                '<div class="col-sm-4 text-right">' + readable + '</div>' +
                '</div>',
                progress: percent
            });

            if (result.nextIndex >= total) {
                finishCleanup(result);
                return;
            }

            if (result.nextIndex > 0 && result.nextIndex < total && running) {
                doCleanup(result.nextIndex);
            }
        });
    }

    function showCleanupNotify(title, message) {
        return createNotify({
            title: title,
            message: message
        }, {
            allow_dismiss: true,
            showProgressbar: true,
            delay: 0,
            onClose: function () {
                stopCleanup();
            }
        });
    }

    function flush(param) {
        var type = (typeof param.data('type') !== 'undefined') ? param.data('type') : 'product';
        return ioCall('clearImageCache', [type, true], function (result) {
            updateStats();
            showGenerateNotify(result.success).update({
                progress: 100,
                message: '&nbsp;',
                type: 'success',
                title: result.success
            });
        });
    }

    function startGenerate(type) {
        running = true;
        lastResults = [];
        lastTick = new Date();
        notify = showGenerateNotify('Bilder werden generiert', 'Statistiken werden berechnet...');

        $('.action-buttons a').attr('disabled', true);
        doGenerate(type, 0);
    }

    function stopGenerate() {
        running = false;
        $('.action-buttons a').attr('disabled', false);
    }

    function finishGenerate() {
        stopGenerate();
        updateStats();

        notify.update({
            progress: 100,
            message: '&nbsp;',
            type: 'success',
            title: 'Bilder erfolgreich generiert'
        });
    }

    function doGenerate(type, index) {
        lastTick = new Date().getTime();
        var call = loadGenerate(type, index, function (result) {
            var items = result.images,
                rendered = result.renderedImages,
                total = result.total,
                offsetTick = new Date().getTime() - lastTick,
                perItem = Math.floor(offsetTick / items.length),
                avg,
                remaining,
                eta,
                readable,
                percent;

            if (lastResults.length >= 10) {
                lastResults.splice(0, 1);
            }
            lastResults.push(perItem);

            avg = average(lastResults);
            remaining = total - rendered;
            eta = Math.max(0, Math.ceil(remaining * avg));
            readable = shortGermanHumanizer(eta);
            percent = Math.round(rendered * 100 / total, 0);

            notify.update({
                message: '<div class="row">' +
                '<div class="col-sm-4"><strong>' + percent + '</strong>%</div>' +
                '<div class="col-sm-4 text-center">' + rendered + ' / ' + total + '</div>' +
                '<div class="col-sm-4 text-right">' + readable + '</div>' +
                '</div>',
                progress: percent
            });

            if (rendered >= total) {
                finishGenerate();
                return;
            }

            if (rendered < total && running) {
                doGenerate(type, rendered);
            }
        });
        $.when(call).done();
    }

    function average(array) {
        var t = 0,
            i = 0;
        while (i < array.length) t += array[i++];
        return t / array.length;
    }

    var shortGermanHumanizer = humanizeDuration.humanizer({
        round: true,
        delimiter: ' ',
        units: ['h', 'm', 's'],
        language: 'shortDE',
        languages: {
            shortDE: {
                h: function () {
                    return 'Std'
                },
                m: function () {
                    return 'Min'
                },
                s: function () {
                    return 'Sek'
                }
            }
        }
    });

    function loadGenerate(type, index, callback) {
        return ioCall('generateImageCache', [type, index], function (result) {
            callback(result);
        });
    }

    function showGenerateNotify(title, message) {
        return createNotify({
            title: title,
            message: message
        }, {
            allow_dismiss: true,
            showProgressbar: true,
            delay: 0,
            onClose: function () {
                stopGenerate();
                updateStats();
            }
        });
    }

    $(function () {
        $('[data-callback]').click(function (e) {
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
    });
</script>
{/literal}
{include file='tpl_inc/footer.tpl'}