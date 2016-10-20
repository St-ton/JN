{if $account}
    </div>
</div>{* /backend-wrapper *}

<script type="text/javascript">
if (typeof CKEDITOR !== 'undefined') {ldelim}
    CKEDITOR.editorConfig = function(config) {ldelim}
         config.language = 'de';
        // config.uiColor = '#AADC6E';
        config.startupMode = '{if isset($Einstellungen.global.admin_ckeditor_mode) && $Einstellungen.global.admin_ckeditor_mode === 'Q'}source{else}wysiwyg{/if}';
        config.htmlEncodeOutput = false;
        config.basicEntities = false;
        config.htmlEncodeOutput = false;
        config.allowedContent = true;
        config.enterMode = CKEDITOR.ENTER_P;
        config.entities = false;
        config.entities_latin = false;
        config.entities_greek = false;
        config.ignoreEmptyParagraph = false;
        config.filebrowserBrowseUrl = '{$PFAD_KCFINDER}browse.php?type=Sonstiges&token={$smarty.session.jtl_token}';
        config.filebrowserImageBrowseUrl = '{$PFAD_KCFINDER}browse.php?type=Bilder&token={$smarty.session.jtl_token}';
        config.filebrowserFlashBrowseUrl = '{$PFAD_KCFINDER}browse.php?type=Videos&token={$smarty.session.jtl_token}';
        config.filebrowserUploadUrl = '{$PFAD_KCFINDER}upload.php?type=Sonstiges&token={$smarty.session.jtl_token}';
        config.filebrowserImageUploadUrl = '{$PFAD_KCFINDER}upload.php?type=Bilder&token={$smarty.session.jtl_token}';
        config.filebrowserFlashUploadUrl = '{$PFAD_KCFINDER}upload.php?type=Videos&token={$smarty.session.jtl_token}';
        config.extraPlugins = 'codemirror';
        config.fillEmptyBlocks = false;
        config.autoParagraph = false;
        {*config.codemirror = {ldelim}*}
            {*mode: 'smartymixed'*}
        {*{rdelim};*}
    {rdelim};
    CKEDITOR.editorConfig(CKEDITOR.config);
{rdelim}
$('.select2').select2();
</script>

{/if}
</body></html>
