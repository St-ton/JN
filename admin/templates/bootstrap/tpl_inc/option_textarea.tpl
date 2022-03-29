<div class="form-group">
    <textarea style="{if isset($setting->textareaAttributes.Resizable)}resize:{$setting->textareaAttributes.Resizable}{/if};max-width:800%;width:100%"
          name="{$setting->elementID}"
          {if isset($setting->textareaAttributes.Cols)}cols="{$setting->textareaAttributes.Cols}"{/if}
          {if isset($setting->textareaAttributes.Rows)}rows="{$setting->textareaAttributes.Rows}"{/if}
          id="{$setting->elementID}"
          class="form-control{if isset($setting->textareaAttributes.Class)} {$setting->textareaAttributes.Class}{/if}"
          placeholder="{__($setting->cPlaceholder)}"
    >{$setting->value|escape:'html'}</textarea>
</div>
