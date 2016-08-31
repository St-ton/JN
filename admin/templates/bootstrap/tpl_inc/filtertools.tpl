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
                <label for="{$oFilter->getId()}_{$oField->getId()}">
                    {$oField->getTitle()}
                </label>
                {if $oField->getType() === 'text'}
                    {if $oField->isCustomTestOp()}
                        <select class="form-control"
                                name="{$oFilter->getId()}_{$oField->getId()}_op"
                                id="{$oFilter->getId()}_{$oField->getId()}_op">
                            {if $oField->getDataType() == 0}
                                <option value="1"{if $oField->getTestOp() == 1} selected{/if}>enth&auml;lt</option>
                                <option value="2"{if $oField->getTestOp() == 2} selected{/if}>beginnt mit</option>
                                <option value="3"{if $oField->getTestOp() == 3} selected{/if}>endet mit</option>
                                <option value="4"{if $oField->getTestOp() == 4} selected{/if}>ist gleich</option>
                                <option value="9"{if $oField->getTestOp() == 9} selected{/if}>ist ungleich</option>
                            {elseif $oField->getDataType() == 1}
                                <option value="4"{if $oField->getTestOp() == 4} selected{/if}>=</option>
                                <option value="9"{if $oField->getTestOp() == 9} selected{/if}>!=</option>
                                <option value="5"{if $oField->getTestOp() == 5} selected{/if}>&lt;</option>
                                <option value="6"{if $oField->getTestOp() == 6} selected{/if}>&gt;</option>
                                <option value="7"{if $oField->getTestOp() == 7} selected{/if}>&lt;=</option>
                                <option value="8"{if $oField->getTestOp() == 8} selected{/if}>&gt;=</option>
                            {/if}
                        </select>
                    {/if}
                    <input type="text" class="form-control"
                           name="{$oFilter->getId()}_{$oField->getId()}"
                           id="{$oFilter->getId()}_{$oField->getId()}"
                           value="{$oField->getValue()}" placeholder="{$oField->getTitle()}">
                {elseif $oField->getType() === 'select'}
                    <select class="form-control"
                            name="{$oFilter->getId()}_{$oField->getId()}"
                            id="{$oFilter->getId()}_{$oField->getId()}">
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
