{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='navigationsfilter'}
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
            '<div class="price-row">' +
                '<button type="button" class="btn-remove-range btn btn-danger btn-sm">' +
                    '<i class="fas fa-trash-alt"></i></button> ' +
                '<label for="nVon_' + n + '">{__('from')}:</label> ' +
                '<input id="nVon_' + n + '" class="form-control" name="nVon[]" type="text" value="' + nVon + '"> ' +
                '<label for="nBis_' + n + '">{__('to')}:</label> ' +
                '<input id="nBis_' + n + '" class="form-control" name="nBis[]" type="text" value="' + nBis + '">' +
            '</div>'
        );

        $('.btn-remove-range').off('click').on('click', removePriceRange);
    }

    function removePriceRange()
    {
        $(this).parent().remove();
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
    <form name="einstellen" method="post" id="einstellen">
        {$jtl_token}
        <input type="hidden" name="speichern" value="1"/>
        <div id="settings">
            {assign var=open value=false}
            {foreach $oConfig_arr as $oConfig}
                {if $oConfig->cConf === 'Y'}
                    <div class="item form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="{$oConfig->cWertName}">{$oConfig->cName}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        {if $oConfig->cInputTyp === 'selectbox'}
                            <select id="{$oConfig->cWertName}" name="{$oConfig->cWertName}"
                                    class="custom-select combo"
                                    {if $oConfig->cWertName === 'preisspannenfilter_anzeige_berechnung'}
                                        onChange="selectCheck(this);"
                                    {/if}>
                                {foreach $oConfig->ConfWerte as $wert}
                                    <option value="{$wert->cWert}"
                                            {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>
                                        {$wert->cName}
                                    </option>
                                {/foreach}
                            </select>
                        {elseif $oConfig->cInputTyp === 'number'}
                            <input class="form-control" type="number" name="{$oConfig->cWertName}"
                                   id="{$oConfig->cWertName}"
                                   value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}"
                                   tabindex="1">
                        {else}
                            <input class="form-control" type="text" name="{$oConfig->cWertName}"
                                   id="{$oConfig->cWertName}"
                                   value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}"
                                   tabindex="1">
                        {/if}
                        </div>
                        {if $oConfig->cBeschreibung}
                            <span class="col-auto ml-sm-n4 order-2 order-sm-3">
                                {getHelpDesc cDesc=$oConfig->cBeschreibung cID=$oConfig->kEinstellungenConf}
                            </span>
                        {/if}
                        {if $oConfig->cWertName === 'preisspannenfilter_anzeige_berechnung'}
                    </div>
                    <div id="Werte" style="display: {if $oConfig->gesetzterWert === 'M'}block{else}none{/if};"
                         class="form-inline">
                        <div id="ranges-error-alert" class="alert alert-danger" style="display: none;"></div>
                        <div id="price-rows"></div>
                        <button type="button" class="btn btn-info btn-sm" id="btn-add-range">
                            <i class="fal fa-plus"></i>
                        </button>
                    </div>
                    <div class="item input-group">
                        {/if}
                    </div>
                {else}
                    {if $oConfig->cName}
                        {if $open}
                    </div>
                </div>
                        {/if}
                <div class="card">
                    <div class="card-header">
                        <div class="subheading1">
                            {$oConfig->cName}
                        </div>
                        <hr class="mb-n3">
                    </div>
                    <div class="card-body">
                        {assign var=open value=true}
                    {/if}
                {/if}
            {/foreach}
            {if $open}
                    </div>
                </div>
            {/if}
        </div>
        <div class="submit card-footer save-wrapper">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <button name="speichern" class="btn btn-primary btn-block" type="submit" value="{__('save')}">
                        {__('saveWithIcon')}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
