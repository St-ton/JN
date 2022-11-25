{if isset($cSearch) && $cSearch|strlen  > 0}
    {assign var=title value=$cSearch}
{/if}
{include file='tpl_inc/seite_header.tpl' cTitel=$title cBeschreibung='' cDokuURL=$cPrefURL}
{$search = isset($cSuche) && !empty($cSuche)}
{if $search}
    <script>
        $(function() {
            var $element = $('.input-group.highlight');
            if ($element.length > 0) {
                var height = $element.height(),
                    offset = $element.offset().top,
                    wndHeight = $(window).height();
                if (height < wndHeight) {
                    offset = offset - ((wndHeight / 2) - (height / 2));
                }

                $('html, body').stop().animate({ scrollTop: offset }, 400);
            }
        });
    </script>
{/if}
<div id="content">
    <div id="settings">
        {if $testResult|default:null !== null}
            <div class="card">
                <div class="card-body">
                    <pre>{$testResult}</pre>
                </div>
            </div>
        {/if}
        {if isset($sections) && count($sections) > 0}
            <form name="einstellen" method="post" action="{$action|default:''}" class="settings navbar-form">
                {$jtl_token}
                <input type="hidden" name="einstellungen_bearbeiten" value="1" />
                {if $search}
                    <input type="hidden" name="cSuche" value="{$cSuche}" />
                    <input type="hidden" name="einstellungen_suchen" value="1" />
                {/if}
                <input type="hidden" name="kSektion" value="{$kEinstellungenSektion}" />
                {include file='tpl_inc/config_sections.tpl'}
                <div class="save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            {foreach $sections as $section}
                                {if $section->getID() === $smarty.const.CONF_EMAILS}
                                    <script>
                                    $(function() {
                                        if ($('#email_methode').val() !== 'smtp') {
                                            $('#configTest').hide();
                                        }
                                        $('#email_methode').on('change', function () {
                                            var currentVal = $(this).val();
                                            if (currentVal === 'smtp') {
                                                $('#configTest').show();
                                            } else {
                                                $('#configTest').hide();
                                            }
                                        });
                                    });
                                    </script>
                                    <button type="submit" name="test_emails" value="1" class="btn btn-secondary btn-block" id="configTest">
                                        {__('saveWithconfigTest')}
                                    </button>
                                {/if}
                            {/foreach}
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button type="submit" value="{__('savePreferences')}" class="btn btn-primary btn-block">
                                {__('saveWithIcon')}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        {else}
            <div class="alert alert-info">{__('noSearchResult')}</div>
        {/if}
    </div>
</div>
