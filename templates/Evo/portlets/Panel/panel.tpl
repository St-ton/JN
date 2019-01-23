{assign var=isPreview value=$isPreview|default:false}
{assign var=data value=[]}
{if $isPreview}
    {assign var=data value=['portlet' => $instance->getDataAttribute()]}
{/if}

{card id=$instance->getProperty('uid')
      header=$instance->getSubareaFinalHtml('pnl_title')|default:null
      footer=$instance->getSubareaFinalHtml('pnl_footer')|default:null
      class=$instance->getProperty('panel-state')
      data=$data
}
    {$instance->getSubareaFinalHtml('pnl_body')}
{/card}
