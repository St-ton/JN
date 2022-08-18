{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('navigationsfilter') cBeschreibung=__('navigationsfilterDesc')
         cDokuURL=__('navigationsfilterUrl')}

<script>
    var bManuell = false;

    $(function()
    {
        $('#einstellen').submit(validateFormData);
        $('#btn-add-range').on('click', function() { addPriceRange(); });
        $('.btn-remove-range').on('click', removePriceRange);

        selectCheck(document.getElementById('preisspannenfilter_anzeige_berechnung'));

        {foreach $oPreisspannenfilter_arr as $i => $oPreisspanne}
            addPriceRange({$oPreisspanne->nVon}, {$oPreisspanne->nBis});
        {/foreach}
    });

    function addPriceRange(nVon, nBis)
    {
        var n = Math.floor(Math.random() * 1000000);

        nVon = nVon || 0;
        nBis = nBis || 0;

        $('#price-rows').append(
            '<div class="price-row row mx-0 justify-content-end">' +
                '<div class="col-5 col-md-4 px-1"><div class="input-group mb-3">' +
                '  <div class="input-group-prepend">' +
                '    <span class="input-group-text">{__('from')}</span>' +
                '  </div>' +
                '  <input id="nVon_' + n + '" class="form-control" name="nVon[]" type="text" value="' + nVon + '"> ' +
                '</div></div>' +
                '<div class="col-5 col-md-4 px-1"><div class="input-group mb-3">'+
                '  <div class="input-group-prepend">'+
                '    <span class="input-group-text">{__('to')}</span>'+
                '  </div>'+
                '  <input id="nBis_' + n + '" class="form-control" name="nBis[]" type="text" value="' + nBis + '"> '+
                '</div></div>' +
                '<div class="col-1 text-right"><button type="button" class="btn-remove-range btn btn-link btn-sm">' +
                '<span class="far fa-trash-alt"></span></button></div>' +
            '</div>'
        );

        $('.btn-remove-range').off('click').on('click', removePriceRange);
    }

    function removePriceRange()
    {
        $(this).closest('.price-row').remove();
    }

    function selectCheck(selectBox)
    {
        if (selectBox.selectedIndex === 1) {
            $('#Werte').show();
            bManuell = true;
        } else if (selectBox.selectedIndex === 0) {
            $('#Werte').hide();
            bManuell = false;
        }
    }

    function validateFormData(e)
    {
        if (bManuell === true) {
            var cFehler = '',
                $priceRows = $('.price-row'),
                lastUpperBound = 0,
                $errorAlert = $('#ranges-error-alert');

            $errorAlert.hide();

            $priceRows
                .sort(function(a, b) {
                    var aVon = parseFloat($(a).find('[id^=nVon_]').val()),
                        bVon = parseFloat($(b).find('[id^=nVon_]').val());
                    return aVon < bVon ? -1 : +1;
                })
                .each(function(i, row) {
                    $('#price-rows').append(row);
                });

            $priceRows.each(function(i, row) {
                var $row  = $(row),
                    $nVon = $row.find('[id^=nVon_]'),
                    $nBis = $row.find('[id^=nBis_]'),
                    nVon  = $nVon.val(),
                    nBis  = $nBis.val(),
                    fVon  = parseFloat(nVon),
                    fBis  = parseFloat(nBis);

                $row.removeClass('has-error');

                if(nVon === '' || nBis === '') {
                    cFehler += '{__('errorFillRequired')}' + '<br>';
                    $row.addClass('has-error');
                } else if(fVon >= fBis) {
                    cFehler += '{__('thePriceRangeIsInvalid')} (' + fVon + ' {__('to')} ' + fBis + ').<br>';
                    $row.addClass('has-error');
                } else if(fVon < lastUpperBound) {
                    cFehler += '{__('thePriceRangeOverlapps')} (' + fVon + ' {__('to')} ' + fBis + ').<br>';
                    $row.addClass('has-error');
                }

                lastUpperBound = fBis;
            });

            if(cFehler !== '') {
                $errorAlert.html(cFehler).show();
                e.preventDefault();
            }
        }
    }
</script>

<div id="content">
    {include file='tpl_inc/config_section.tpl'
        name='einstellen'
        a='saveSettings'
        action=$adminURL|cat:$route
        buttonCaption=__('saveWithIcon')
        title=__('settings')
        tab='einstellungen'
    }
</div>
{include file='tpl_inc/footer.tpl'}
