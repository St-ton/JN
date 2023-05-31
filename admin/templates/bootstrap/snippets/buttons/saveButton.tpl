{$type = $type|default:'submit'}
{$name = $name|default:'saveAndContinue'}
{$value = $value|default:'1'}
{$class = $class|default:'btn btn-primary btn-block'}
{$id = $id|default:'save'}
{$content = $content|default:'<i class="fal fa-save"></i> '|cat:__('save')}
{$scrollFunction = $scrollFunction|default:false}

{if $scrollFunction === true}
    <input type="hidden" name="scrollPosition" value="">
{/if}
<button type="{$type}" name="{$name}" value="{$value}" class="{$class}" id="{$id}">
    {$content}
</button>
