<form id="surcharge-form-{$zuschlagliste->getID()}">
    <input type="hidden" name="kVersandzuschlag" value="{$zuschlagliste->getID()}"/>
    <div class="row">
        <div class="col-md-5 text-right">Zuschlagliste:</div>
        <div class="col-md-7 text-left"><input name="cName" value="{$zuschlagliste->getTitle()}"/></div>
    </div>
    <div class="row">
        <div class="col-md-5 text-right">Zuschlag:</div>
        <div class="col-md-7 text-left"><input name="fZuschlag" value="{$zuschlagliste->getSurcharge()}"/></div>
    </div>
    {foreach $sprachen as $sprache}
        <div class="row">
            <div class="col-md-5 text-right">{__('showedName')} ({$sprache->cNameDeutsch}):</div>
            <div class="col-md-7 text-left">
                <input type="text" name="cName_{$sprache->cISO}" value="{$zuschlagliste->getName($sprache->kSprache)}"/>
            </div>
        </div>
    {/foreach}
    <div class="row">
        <div class="col-md-5 text-right">PLZ:</div>
        <div class="col-md-7 text-left">
            {include file="snippets/zuschlagliste_plz_badges.tpl"}
        </div>
    </div>
    <div class="row">
        <div class="col-md-5 text-right"></div>
        <div class="col-md-7 text-left">
            <button class="btn btn-sm surcharge-update" data-surcharge-id="{$zuschlagliste->getID()}"><i class="fa fa-edit"></i></button>
            <button class="btn btn-sm surcharge-remove" data-surcharge-id="{$zuschlagliste->getID()}"><i class="fa fa-trash"></i></button>
            <button type="button" class="btn btn-sm" data-toggle="collapse" data-target="#collapse-surcharge-zip-add-{$zuschlagliste->getID()}"><i class="fa fa-chevron-down"></i></button>
        </div>
    </div>
</form>
<div class="row collapse" id="collapse-surcharge-zip-add-{$zuschlagliste->getID()}">
    <div class="alert-message"></div>
    <form id="add-zip-{$zuschlagliste->getID()}">
        <input type="hidden" name="kVersandzuschlag" value="{$zuschlagliste->getID()}">
        {__('plz')} <input type="text" name="cPLZ" class="form-control zipcode" /> {__('orPlzRange')}
        <div class="input-group">
            <input type="number" name="cPLZAb" class="form-control zipcode" />
            <span class="input-group-addon">&ndash;</span>
            <input type="number" name="cPLZBis" class="form-control zipcode" />
        </div>
        <button type="submit" class="btn btn-sm">PLZ hinzufügen</button>
    </form>
</div>

<script>
    $('.surcharge-remove[data-surcharge-id="{$zuschlagliste->getID()}"]').click(function (e) {
        e.preventDefault();
        ioCall('deleteZuschlagsListe', [$(this).data('surcharge-id')], function (data) {
            if (data.surchargeID > 0) {
                $('.surcharge-box[data-surcharge-id="' + data.surchargeID + '"]').remove();
            }
        });
    });
    $('.surcharge-update[data-surcharge-id="{$zuschlagliste->getID()}"]').click(function(e){
        e.preventDefault();
        ioCall('updateZuschlagsListe', [$('#surcharge-form-{$zuschlagliste->getID()}').serializeArray()], function (data) {
            //TODO
        });
    });
    $('#add-zip-{$zuschlagliste->getID()} button[type="submit"]').click(function(e){
        e.preventDefault();
        ioCall('createZuschlagsListeZIP', [$('#add-zip-{$zuschlagliste->getID()}').serializeArray()], function (data) {
            $('#collapse-surcharge-zip-add-{$zuschlagliste->getID()} .alert-message').html(data.message);
            $('#zip-badge-{$zuschlagliste->getID()}').html(data.badges);
            setBadgeClick(data.surchargeID);
        });
    });

    function setBadgeClick(surchargeID) {
        $('.zip-badge[data-surcharge-id="' + surchargeID + '"]').click(function(e){
            e.preventDefault();
            ioCall('deleteZuschlagsListeZIP', [$(this).data('surcharge-id'), $(this).data('zip')], function (data) {
                $('.zip-badge[data-surcharge-id="' + data.surchargeID + '"][data-zip="' + data.ZIP + '"]').remove();
            });
        });
    }
    setBadgeClick({$zuschlagliste->getID()});
</script>

