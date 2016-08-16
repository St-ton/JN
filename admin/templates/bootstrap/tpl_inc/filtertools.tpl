{if !isset($cParam_arr)}
    {assign var=cParam_arr value=[]}
{/if}

<div class="block">
    <form method="get" class="form-inline">
        {foreach $cParam_arr as $cParamName => $cParamValue}
            <input type="hidden" name="{$cParamName}" value="{$cParamValue}">
        {/foreach}
        {foreach $oFilter->getFields() as $oField}
            <div class="form-group">
                <label class="sr-only" for="{$oField->getColumn()}">{$oField->getTitle()}</label>
                {if $oField->getType() === 'text'}
                    <input type="text" class="form-control"
                           name="{$oFilter->getId()}_{$oField->getColumn()}"
                           id="{$oFilter->getId()}_{$oField->getColumn()}"
                           value="{$oField->getValue()}" placeholder="{$oField->getTitle()}">
                {elseif $oField->getType() === 'select'}
                    <select class="form-control"
                            name="{$oFilter->getId()}_{$oField->getColumn()}"
                            id="{$oFilter->getId()}_{$oField->getColumn()}">
                        {foreach $oField->getOptions() as $i => $oOption}
                            <option value="{$i}"{if $i == (int)$oField->getValue()} selected{/if}>{$oOption->getTitle()}</option>
                        {/foreach}
                    </select>
                {/if}
            </div>
        {/foreach}
        <div class="btn-group">
            <button type="submit" class="btn btn-primary" name="action" value="{$oFilter->getId()}_filter">
                <i class="fa fa-search"></i>
            </button>
            <button type="submit" class="btn btn-default" name="action" value="{$oFilter->getId()}_resetfilter">
                <i class="fa fa-eraser"></i>
            </button>
        </div>
    </form>
</div>
