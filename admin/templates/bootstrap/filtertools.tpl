{if !isset($cParam_arr)}
    {assign var=cParam_arr value=[]}
{/if}

<div class="block">
    <form method="get" class="form-inline">
        {foreach $cParam_arr as $cParamName => $cParamValue}
            <input type="hidden" name="{$cParamName}" value="{$cParamValue}">
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
        <div class="btn-group">
            <button type="submit" class="btn btn-primary" name="action" value="filter">
                <i class="fa fa-search"></i>
            </button>
            <button type="submit" class="btn btn-danger" name="action" value="resetfilter">
                <i class="fa fa-eraser"></i>
            </button>
        </div>
    </form>
</div>
