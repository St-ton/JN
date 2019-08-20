<h{$instance->getProperty('level')} style="{$instance->getStyleString()}"
                                    {if $isPreview}data-portlet="{$instance->getDataAttribute()}"{/if}
                                    class="{$instance->getAnimationClass()}"
                                    {$instance->getAnimationDataAttributeString()}>
    {$instance->getProperty('text')}
</h{$instance->getProperty('level')}>