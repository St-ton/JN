{include file='tpl_inc/seite_header.tpl' cTitel=#lang# cBeschreibung=#langDesc# cDokuURL=#langURL#}
<div id="content" class="container-fluid">
    <div class="panel panel-default settings">
        <div class="panel-heading">
            <h3 class="panel-title">Neue Sprachvariable</h3>
        </div>
        <div class="panel-body">
            <div class="input-group">
                <span class="input-group-addon">
                    <label for="kSprachsektion">Sprachsektion</label>
                </span>
                <span class="input-group-wrap">
                    <select class="form-control" name="kSprachsektion" id="kSprachsektion">
                        {foreach $oSektion_arr as $oSektion}
                            <option value="{$oSektion->kSprachsektion}">{$oSektion->cName}</option>
                        {/foreach}
                    </select>
                </span>
            </div>
            <div class="input-group">
                <span class="input-group-addon">
                    <label for="cName">Variable</label>
                </span>
                <span class="input-group-wrap">
                    <input type="text" class="form-control" name="cName" id="cName">
                </span>
            </div>
            {foreach $oSprache_arr as $oSprache}
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="cWert_{$oSprache->cISO}">{$oSprache->cNameDeutsch}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="text" class="form-control" name="cWert_{$oSprache->cISO}"
                               id="cWert_{$oSprache->cISO}">
                    </span>
                </div>
            {/foreach}
        </div>
        <div class="panel-footer">
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i>
                    Speichern
                </button>
            </div>
        </div>
    </div>
</div>