<div class='form-group'>
    <label for="config-{$propname}">{$propdesc.label}</label>
    <textarea name="{$propname}" id="config-{$propname}" class="form-control" {if $required}required{/if}>{htmlspecialchars($propval)}</textarea>
    <script>
        var adminLang = '{\JTL\Shop::Container()->getGetText()->getLanguage()}'.toLowerCase();

        if(!CKEDITOR.lang.languages.hasOwnProperty(adminLang)) {
            adminLang = adminLang.split('-')[0]
        }

        CKEDITOR.replace(
            'config-{$propname}',
            {
                baseFloatZIndex: 9000,
                language: adminLang,
                filebrowserBrowseUrl: BACKEND_URL + 'elfinder?ckeditor=1&token=' + JTL_TOKEN + '&mediafilesType=image',
                /* custom config */
                toolbarGroups:[
                    { name: 'clipboard', groups: ['clipboard', 'undo']},
                    { name: 'editing', groups: ['find', 'selection', 'spellchecker']},
                    { name: 'links'},
                    { name: 'insert'},
                    { name: 'forms'},
                    { name: 'tools'},
                    { name: 'document', groups: ['mode', 'document', 'doctools']},
                    { name: 'others'},
                    '/',
                    { name: 'basicstyles', groups: ['basicstyles', 'cleanup']},
                    { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi']},
                    { name: 'styles'},
                    { name: 'colors'},
                    { name: 'about'}
                ],
                format_tags:'p;h1;h2;h3;pre',
                removeDialogTabs:'image:advanced;link:upload;image:Upload',
                allowedContent : true,
                htmlEncodeOutput : false,
                basicEntities : false,
                enterMode : CKEDITOR.ENTER_P,
                entities : false,
                entities_latin : false,
                entities_greek : false,
                ignoreEmptyParagraph : false,
                fillEmptyBlocks : false,
                autoParagraph : false,
                removePlugins : 'exportpdf,language,iframe,flash'
                /* custom config end */
            },
        );

        $.each(CKEDITOR.dtd.$removeEmpty, key => {
            CKEDITOR.dtd.$removeEmpty[key] = false;
        });

        opc.once('save-config', () => {
            $('#config-{$propname}').val(CKEDITOR.instances['config-{$propname}'].getData());
        });
    </script>
</div>
