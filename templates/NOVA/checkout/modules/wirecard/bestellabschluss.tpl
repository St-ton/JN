<div style="margin:10px 0;">
   {strip}
   <div>
      {image src="{$currentTemplateDir}../../gfx/Wirecard/logo.gif" alt="Wirecard Logo"}
   </div>
   {/strip}
   <div style="margin:10px 0;">
      {lang key='payWithWirecard'}
   </div>
   <div>
      {form method="post" action="{$url}"}
         {foreach $fields as $value}
            {input type="hidden" name="{$name}" value="{$value}"}
         {/foreach}
         {input type="submit" value="{$submitCaption}"}
      {/form}
   </div>
</div>