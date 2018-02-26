$(document).ready(function() {

    $('#nAnimationSpeed, #nPauseTime').change(function() {
        var nAnimationSpeed = parseInt($('#nAnimationSpeed').val()),
            nPauseTime = parseInt($('#nPauseTime').val());
        if(nAnimationSpeed > nPauseTime) {
            $('#nAnimationSpeedWarning').show();
            $('#nAnimationSpeed').addClass('nAnimationSpeedWarningBorder');
        } else {
            if($('#nAnimationSpeed').hasClass('nAnimationSpeedWarningBorder')) {
                $('#nAnimationSpeedWarning').hide();
                $('#nAnimationSpeed').removeClass('nAnimationSpeedWarningBorder');
            }
        }
    });

    $('.random_effects').click(function() {
        if($('#cRandomEffects').prop('checked')){
            $('select[name=cSelectedEffects]').attr('disabled',true);
            $('select[name=cAvaibleEffects]').attr('disabled',true);
            $('button.select_add').attr('disabled',true);
            $('button.select_remove').attr('disabled',true);
            $('input[type=hidden][name=cEffects]').attr('disabled',true);
            $('select[name=cSelectedEffects]').html('');
        } else {
            $('select[name=cSelectedEffects]').removeAttr('disabled');
            $('select[name=cAvaibleEffects]').removeAttr('disabled');
            $('button.select_add').removeAttr('disabled');
            $('button.select_remove').removeAttr('disabled');
            $('input[type=hidden][name=cEffects]').removeAttr('disabled');
        }
    });

    $('form#slider').submit(function() {
        if( $('.random_effects').prop('checked') !== true){
            var effects = new Array();
            $.each($('select[name=cSelectedEffects] option'), function(index,value) {
                effects[index] = $(this).val();
            });
            $('input[name=cEffects]').val(effects.join(';'));
        }
    });

    $('button.select_add').click(function() {
        $.each($('select[name=cAvaibleEffects]').val(), function(index,value) {
            var exists = false,
                html;
            $.each($('select[name=cSelectedEffects] option'), function(element) {
                if($(this).val() == value) {
                    exists = true;
                }
            });

            if(exists == false) {
                html = '<option value="'+value+'">'+value+'</option>';
                $('select[name=cSelectedEffects]').append(html);
            } else {
                alert('Der Eintrag mit den Wert "'+value+'" existiert bereits!');
            }
        });
    });

    $('button.select_remove').click(function() {
        $.each($('select[name=cSelectedEffects] option:selected'), function(index,value) {
            $(this).remove();
        });
    });

    $('#nSeitenTyp').change(filterConfigUpdate);
    $('#cKey').change(filterConfigUpdate);

    filterConfigUpdate();

});

function filterConfigUpdate()
{
    var $nSeitenTyp = $('#nSeitenTyp');
    var $type2      = $('#type2');
    var $nl         = $('.nl');
    var $cKey       = $('#cKey');

    $nl.hide();
    $('.key').hide();
    $type2.hide();

    switch ($nSeitenTyp.val()) {
        case '1':
            $nl.show();
            $('#keykArtikel').show();
            $cKey.val('');
            break;
        case '2':
            $type2.show();
            if ($cKey.val() !== '') {
                $('#key' + $cKey.val()).show();
                $nl.show();
            }
            break;
        case '31':
            $nl.show();
            $('#keykLink').show();
            $cKey.val('');
            break;
        default:
            $cKey.val('');
            break;
    }
}

function hideOverlayEdit(id) {
    $('li#'+id).find('div.overlay_edit').fadeOut('fast');
}

function hideOverlay(id) {
    var elem = $('li#'+id);
    elem.removeClass('active').addClass('inactive');
    elem.find('div.overlay').fadeOut('fast');
    hideOverlayEdit(id);
}

//
$(function() {
    $("#tableSlide tbody ").sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        opacity : '0',
        axis : "y",
        cursor: "move",
        cursorAt : {top: 5},
        stop : function(item) {
            sortSlide();
        }
    });
});

function select_image( key ) {
    var id = key,
        shop_url = $('.shop_url').html(),
        kcfinder_path = $('.kcfinder_path').html();

    window.KCFinder = {
        callBack: function(url) {
            $('#img'+id).attr('src', url);
            $('input[name="aSlide\['+id+'\]\[cBild\]"]').val(url);
            kcFinder.close();
        }
    };
    var kcFinder = window.open(kcfinder_path+'browse.php?type=Bilder&lang=de', 'kcfinder_textbox','status=0, toolbar=0, location=0, menubar=0, directories=0, resizable=1, scrollbars=0, width=800, height=600,');
}

var count = 0;
function addSlide(slide) {
    var new_slide = $('#newSlide').html();
    new_slide = new_slide.replace(/NEU/g, "neu"+count);
    $('#tableSlide tbody').append( new_slide );
    count++;
    sortSlide();
}

function sortSlide() {
    $("input[name*='\[nSort\]']").each(function(index) {
    $(this).val(index+1);
    });
}
//

function bindOverlay() {
    if($(this).find('div.overlay_edit').css('display') === 'none') {
        $(this).find('div.overlay').fadeIn('fast');
        $(this).find('div.overlay_edit').fadeIn('fast');
    }
}