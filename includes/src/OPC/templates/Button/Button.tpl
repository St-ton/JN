{if $isPreview === false}
    {$href = $instance->getProperty('url')}
{/if}

{if $instance->getProperty('size') !== 'md'}
    {$size = $instance->getProperty('size')}
{/if}

{if $instance->getProperty('align') === 'block'}
    {$block = true}
{/if}

{if $instance->getProperty('new-tab') === true}
    {$target = '_blank'}
{/if}

<div {if $isPreview}data-portlet="{$instance->getDataAttribute()}"{/if}
        {if $instance->getProperty('align') !== 'block'}
            style="text-align: {$instance->getProperty('align')}"
        {/if}>
    {button href=$href|default:null
            target=$target|default:null
            size=$size|default:null
            block=$block|default:false
            variant=$instance->getProperty('style')
            title=$instance->getProperty('link-title')|default:null
            class=$instance->getAnimationClass()
            data=$instance->getAnimationData()
            style=$instance->getStyleString()
    }
        {if $instance->getProperty('use-icon') === true && $instance->getProperty('icon-align') === 'left'}
            <i class="{$instance->getProperty('icon')}" style="top:2px;"></i>
        {/if}

        {$instance->getProperty('label')}

        {if $instance->getProperty('use-icon') === true && $instance->getProperty('icon-align') === 'right'}
            <i class="{$instance->getProperty('icon')}" style="top:2px;"></i>
        {/if}
    {/button}
</div>