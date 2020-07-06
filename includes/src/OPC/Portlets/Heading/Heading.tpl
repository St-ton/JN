{$htag = 'h'|cat:$instance->getProperty('level')}

<{$htag} style="{$instance->getStyleString()}"
         class="{$instance->getAnimationClass()} {$instance->getStyleClasses()}"
         {$instance->getAnimationDataAttributeString()}>
    {$instance->getProperty('text')|escape:'html'}
</{$htag}>