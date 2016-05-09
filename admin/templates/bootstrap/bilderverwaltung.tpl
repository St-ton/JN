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
            <th class="text-center" width="200">Aktionen</th>
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
                  <a class="btn btn-default" href="bilderverwaltung.php?action=clear&type={$item->type}"><i class="fa fa-trash-o"></i> Cache leeren</a>
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
  $(function() {
    updateStats();
  });
  
  function updateStats() {
    $('#cache-items tbody > tr').each(function(i, item) {
      var type = $(item).data('type');
      loadStats(type, function(stats) {
        $('.item-total', item).text(stats.total);
        $('.item-corrupted', item).text(stats.corrupted);
        $('.item-total-size', item).text(formatSize(stats.totalSize));
        
        var totalCached = 0;
        
        $(['xs', 'sm', 'md', 'lg']).each(function(i, size) {
          totalCached += stats.generated[size];
        });
        $('.item-generated', item)
          .text(Math.round(totalCached / 4, 0));
      });
    });
  }
  
  function loadStats(type, callback) {
    return ajaxCall('bilderverwaltung.php', { action: 'stats', type: type }, function(result, xhr) {
      if (typeof callback === 'function') {
        callback(result.data);
      }
    });
  }
  
  var lastResults = null;
  var lastTick = null;
  var running = false;
  var notify = null;
  
  function generate() {
    startGenerate('product');
  }
  
  function startGenerate(type) {
    running = true;
    lastResults = [];
    lastTick = new Date();
  
    notify = showGenerateNotify(
      'Bilder werden generiert', 
      'Statistiken werden berechnet...');
  
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
    var call = loadGenerate(type, index, function(result) { 
      var items = result.images;
      var rendered = result.nextIndex;
      var total = result.total;
      
      var offsetTick = new Date().getTime() - lastTick;
      var perItem = Math.floor(offsetTick / items.length);
      
      if (lastResults.length >= 10) {
        lastResults.splice(0, 1);
      }
      lastResults.push(perItem);
  
      var avg = average(lastResults);
      var remaining = total - rendered;
      var eta = Math.max(0, Math.ceil(remaining * avg));
  
      var readable = shortGermanHumanizer(eta);
      var percent = Math.round(rendered * 100 / total, 0);
  
      notify.update({
        message: '<div class="row">' +
          '<div class="col-sm-4"><strong>'+percent+'</strong>%</div>' + 
          '<div class="col-sm-4 text-center">' + rendered + ' / ' + total + '</div>' +
          '<div class="col-sm-4 text-right">'+readable+'</div>' +
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
    var t = 0, i = 0;
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
        h: function() { return 'Std' },
        m: function() { return 'Min' },
        s: function() { return 'Sek' }
      }
    }
  });
  
  function loadGenerate(type, index, callback) {
    return ajaxCall('bilderverwaltung.php', { action: 'cache', type: type, index: index }, function(result, xhr) {
      if (typeof callback === 'function') {
        callback(result.data);
      }
    });
  }
  
  function showGenerateNotify(title, message) {
    return createNotify({
      title: title,
      message: message
    },{
      allow_dismiss: true,
      showProgressbar: true,
      delay: 0,
      onClose: function() {
        stopGenerate();
        updateStats();
      }
    });
  }
  
  $(function() {
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
  });
</script>

{include file='tpl_inc/footer.tpl'}