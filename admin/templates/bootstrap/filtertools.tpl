<div class="block">
    <form method="get" class="form-inline">
        {foreach $oFilter->cGetVar_arr as $cGetVarName => $cGetVarValue}
            <input type="hidden" name="{$cGetVarName}" value="{$cGetVarValue}">
        {/foreach}
        {foreach $oFilter->oField_arr as $oField}
            <div class="form-group">
                <label class="sr-only" for="{$oField->cColumn}">{$oField->cTitle}</label>
                {if $oField->cType === 'text'}
                    <input type="text" class="form-control" name="{$oField->cColumn}" id="{$oField->cColumn}" value="{$oField->cValue}" placeholder="{$oField->cTitle}">
                {elseif $oField->cType === 'select'}
                    <select class="form-control" name="{$oField->cColumn}" id="{$oField->cColumn}">
                        {foreach $oField->oOption_arr as $i => $oOption}
                            <option value="{$i}"{if $i == (int)$oField->cValue} selected{/if}>{$oOption->cTitle}</option>
                        {/foreach}
                    </select>
                {/if}
            </div>
        {/foreach}
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-search"></i> Filtern
        </button>
    </form>
</div>
