{$postfix = $postfix|default:''}
{$prefix = $prefix|default:''}
{$isChild = $isChild|default:false}
{$addChild = $addChild|default:false}

{foreach $item->getAttributes() as $attr}
    {if $attr->isDynamic() === true}
        {continue}
    {/if}
    {$name = $attr->getName()}
    {$inputName = $name}
    {if $isChild}
        {$inputName = $prefix|cat:'['|cat:$inputName|cat:'][]'}
    {/if}
    {$type = $attr->getDataType()}
    {$inputConfig = $attr->getInputConfig()}

    {if $inputConfig->isHidden() === true}
        {input type='hidden' value=$item->getAttribValue($name) name=$inputName id=$name|cat:$postfix}
        {continue}
    {/if}
    {if strpos($type, "\\") !== false && class_exists($type)}
        {$cnt = 0}
        {if $item->$name !== null}
            {$cnt = $item->$name->count()}
        {/if}
        {if $cnt === 0 && $addChild === false}
            {continue}
        {/if}
        <div class="subheading1">{__('childHeading')}</div>
        <hr>
        {foreach $item->$name as $childItem}
            {include file='tpl_inc/model_item.tpl' isChild=true postfix=$childItem->getId() item=$childItem prefix=$name}
            <hr>
        {/foreach}
        {if $addChild !== false && $childModel !== null}
            {include file='tpl_inc/model_item.tpl' isChild=true item=$childModel addChild=false postfix=$postfix prefix=$name assign=cmdata}
            <div id="childmodelappend"></div>
            <script>
                $(document).ready(function () {
                    $('#add-child-model-item').on('click', function () {
                        $('#childmodelappend').append(`{trim($cmdata)}`);
                    });
                });
            </script>

            <button type="button" value="1" class="btn btn-default" id="add-child-model-item">
                <i class="fas fa-share"></i> {__('add')}
            </button>
        {/if}
        {continue}
    {/if}

    {$inputType = $inputConfig->getInputType()}

    <div class="form-group form-row align-items-center">
        {if $inputType === JTL\Plugin\Admin\InputType::SELECT}
            <label class="col col-sm-4 col-form-label text-sm-right" for="{$name}{$postfix}">{__($name)}:</label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <select class="custom-select" id="{$name}{$postfix}" name="{$inputName}">
                    {foreach $inputConfig->getAllowedValues() as $k => $v}
                        <option value="{$k}"{if $item->getAttribValue($name) === $k} selected{/if}>{__($v)}</option>
                    {/foreach}
                </select>
                <span id="specialLinkType-error" class="hidden-soft error"> <i title="{__('isDuplicateSpecialLink')}" class="fal fa-exclamation-triangle error"></i></span>
            </div>
        {elseif $inputType === JTL\Plugin\Admin\InputType::TEXTAREA}
            <label class="col col-sm-4 col-form-label text-sm-right" for="{$name}{$postfix}">{__($name)}:</label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <textarea class="form-control ckeditor" id="{$name}{$postfix}" name="{$inputName}" rows="10" cols="40">{$item->getAttribValue($name)}</textarea>
            </div>
        {else}
            <label class="col col-sm-4 col-form-label text-sm-right" for="{$name}{$postfix}">
                {__({$name})}{if $name === 'languageID'}
                    {foreach $availableLanguages as $availableLanguage}
                        {if $availableLanguage->id === (int)$item->getAttribValue($name)}
                            ({$availableLanguage->localizedName})
                        {/if}
                    {/foreach}
                {/if}:
            </label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                {input readonly=!$inputConfig->isModifyable() type=$inputType value=$item->getAttribValue($name) name=$inputName id=$name|cat:$postfix}
            </div>
        {/if}
    </div>
{/foreach}
