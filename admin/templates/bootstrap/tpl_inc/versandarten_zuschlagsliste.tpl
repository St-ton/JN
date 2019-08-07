{assign var=isleListFor value=__('isleListFor')}
{assign var=cVersandartName value=$Versandart->cName}
{assign var=cLandName value=$Land->getName()}
{assign var=cLandISO value=$Land->getISO()}

{include file='tpl_inc/seite_header.tpl'
         cTitel=$isleListFor|cat: ' '|cat:$cVersandartName|cat:', '|cat:$cLandName|cat:'('|cat:$cLandISO|cat:')'
         cBeschreibung=__('isleListsDesc')}

<div class="card">
    <div class="card-body">
        <button id="zuschlag-new-submit"
                type="submit"
                class="btn btn-primary"
                data-toggle="modal"
                data-target="#new-surcharge-modal">
            <i class="fa fa-save"></i> {__('create')}
        </button>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="table-responsive list-unstyled-inden">
            <table class="table">
                <thead>
                    <tr>
                        <th>{__('name')}</th>
                        <th class="text-center">{__('additionalFee')}</th>
                        <th>{__('zip')}</th>
                        <th class="text-center">{__('actions')}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $surcharges as $surcharge}
                        <tr class="surcharge-box" data-surcharge-id="{$surcharge->getID()}">
                            <td class="surcharge-title">{$surcharge->getTitle()}</td>
                            <td class="surcharge-surcharge text-center">{$surcharge->getPriceLocalized()}</td>
                            <td class="zip-badge-row">{include file="snippets/zuschlagliste_plz_badges.tpl"}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-sm surcharge-remove"
                                            data-surcharge-id="{$surcharge->getID()}">
                                        <span class="icon-hover">
                                            <span class="fal fa-trash-alt"></span>
                                            <span class="fas fa-trash-alt"></span>
                                        </span>
                                    </button>
                                    <button class="btn btn-link px-2" title="{__('add')}"
                                            data-toggle="modal"
                                            data-target="#add-zip-modal"
                                            data-surcharge-name="{$surcharge->getName()}"
                                            data-surcharge-id="{$surcharge->getID()}">
                                        <span class="icon-hover">
                                            <span class="fal fa-plus"></span>
                                            <span class="fas fa-plus"></span>
                                        </span>
                                    </button>
                                    <button class="btn btn-link px-2" title="{__('modify')}"
                                            data-toggle="modal"
                                            data-target="#new-surcharge-modal"
                                            data-surcharge-name="{$surcharge->getName()}"
                                            data-surcharge-id="{$surcharge->getID()}">
                                        <span class="icon-hover">
                                            <span class="fal fa-edit"></span>
                                            <span class="fas fa-edit"></span>
                                        </span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="add-zip-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fa fa-times"></i>
                </button>
                <div class="subheading1">{__('surchargeListFor')} <span id="add-zip-modal-title"></span></div>
            </div>
            <hr class="mb-3">
            <div class="modal-body">
                <div id="add-zip-notice"></div>
                <form id="add-zip-form">
                    <input type="hidden" id="add-zip-modal-id" name="kVersandzuschlag" value="">
                    {__('plz')} <input type="text" name="cPLZ" class="form-control zipcode" /> {__('orPlzRange')}
                    <div class="input-group">
                        <input type="number" name="cPLZAb" class="form-control zipcode" />
                        <span class="input-group-addon">&ndash;</span>
                        <input type="number" name="cPLZBis" class="form-control zipcode" />
                    </div>
                    <div class="row mt-2">
                        <div class="ml-auto col-sm-6 col-lg-auto mb-2">
                            <button type="button" class="btn btn-outline-primary" data-dismiss="modal">
                                {__('cancelWithIcon')}
                            </button>
                        </div>
                        <div class="col-sm-6 col-lg-auto">
                            <button type="submit" class="btn btn-outline-primary">
                                {__('addZIP')}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="new-surcharge-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fa fa-times"></i>
                </button>
                <div class="subheading1">{__('createNewList')}</div>
            </div>
            <hr class="mb-3">
            <div class="modal-body">
                <div id="new-surcharge-form-wrapper">
                    {include file='snippets/zuschlagliste_form.tpl'}
                </div>
            </div>
        </div>
    </div>
</div>



<script>
    $('button[data-target="#add-zip-modal"]').click(function () {
        $('#add-zip-modal-title').html($(this).data('surcharge-name'));
        $('#add-zip-modal-id').val($(this).data('surcharge-id'));
    });
    $('.surcharge-box button[data-target="#new-surcharge-modal"]').click(function () {
        console.log('gsodihj');
        $('#new-surcharge-modal-title').html($(this).data('surcharge-name'));
        $('#new-surcharge-form-wrapper').html('');
        // $('#new-surcharge-modal-id').val($(this).data('surcharge-id'));
        ioCall('getSurcharge', [$(this).data('surcharge-id')], function (data) {
            $('#new-surcharge-form-wrapper').html(data.body);
            console.log(data);
        });
    });

    $('#add-zip-modal button[type="submit"]').click(function(e){
        e.preventDefault();
        ioCall('createZuschlagsListeZIP', [$('#add-zip-form').serializeArray()], function (data) {
            $('#add-zip-notice').html(data.message);
            $('.surcharge-box[data-surcharge-id="' + data.surchargeID + '"] .zip-badge-row').html(data.badges);
            setBadgeClick(data.surchargeID);
        });
    });

    $('.surcharge-remove').click(function (e) {
        e.preventDefault();
        ioCall('deleteZuschlagsListe', [$(this).data('surcharge-id')], function (data) {
            if (data.surchargeID > 0) {
                $('.surcharge-box[data-surcharge-id="' + data.surchargeID + '"]').remove();
            }
        });
    });


    function setBadgeClick(surchargeID) {
        let surchargeIDText = '';
        if  (surchargeID !== 0) {
            surchargeIDText = '[data-surcharge-id="' + surchargeID + '"]';
        }
        $('.zip-badge' + surchargeIDText).click(function(e){
            e.preventDefault();
            ioCall('deleteZuschlagsListeZIP', [$(this).data('surcharge-id'), $(this).data('zip')], function (data) {
                $('.zip-badge[data-surcharge-id="' + data.surchargeID + '"][data-zip="' + data.ZIP + '"]').remove();
            });
        });
    }
    setBadgeClick(0);
</script>