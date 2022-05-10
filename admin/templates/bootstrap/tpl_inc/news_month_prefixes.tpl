{foreach $oNewsMonatsPraefix_arr as $oNewsMonatsPraefix}
    <div class="form-group form-row align-items-center mb-5 mb-md-3">
        <label class="col col-sm-4 col-form-label text-sm-right" for="praefix_{$oNewsMonatsPraefix->cISOSprache}">{__('newsPraefix')} ({$oNewsMonatsPraefix->name})</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
            <input type="text" class="form-control" id="praefix_{$oNewsMonatsPraefix->cISOSprache}" name="praefix_{$oNewsMonatsPraefix->cISOSprache}" value="{$oNewsMonatsPraefix->cPraefix}" tabindex="1" />
        </div>
    </div>
{/foreach}
