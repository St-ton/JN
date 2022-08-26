{assign var='type' value=$type|default:'submit'}
{assign var='name' value=$name|default:'speichern_und_weiter_bearbeiten'}
{assign var='value' value=$value|default:'save-config-continue'}
{assign var='class' value=$class|default:'btn btn-outline-primary btn-block'}
{assign var='id' value=$id|default:'save-and-continue'}
{assign var='content' value=$content|default:'<i class="fal fa-save"></i> '|cat:__('saveAndContinue')}
{assign var='scrollFunction' value=$scrollFunction|default:true}

{if $scrollFunction === true}
    <input type="hidden" name="scrollPosition" value="" />
{/if}
<button type="{$type}" name="{$name}" value="{$value}" class="{$class}" id="{$id}">
    {$content}
</button>