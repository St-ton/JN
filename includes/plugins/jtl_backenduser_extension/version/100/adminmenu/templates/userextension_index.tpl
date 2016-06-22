{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{if $sectionPersonal === true}
    <script type="text/javascript">
        {literal}
        $(document).ready(function() {
            $('#useGravatar').bind('click', function() {
                if ($(this).is(':checked')) {
                    $('#useGravatarDetails').show();
                } else {
                    $('#useGravatarDetails').hide();
                }
            });
        });
        {/literal}
    </script>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Pers&ouml;nliche Angaben</h3>
        </div>
        <div class="panel-body">
            {if $showAvatar === true}
                <div class="item">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="useGravatar">Gravatar benutzen</label>
                        </span>
                        <span class="input-group-wrap">
                            <span class="input-group-checkbox-wrap">
                                <input class="" type="checkbox" id="useGravatar" name="extAttribs[useGravatar]" value="Y" {if isset($attribValues.useGravatar) && $attribValues.useGravatar->cAttribValue === 'Y'}checked {/if}/>
                            </span>
                        </span>
                        <div id="useGravatarDetails"{if !isset($attribValues.useGravatar) || $attribValues.useGravatar->cAttribValue !== 'Y'} class="hidden-soft"{/if}>
                            <span class="input-group-addon">
                                <label for="useGravatarEmail">Abweichende E-Mail Adresse</label>
                            </span>
                            <span class="input-group-wrap">
                                <input id="useGravatarEmail" class="form-control" type="text" name="extAttribs[useGravatarEmail]" value="{if isset($attribValues.useGravatarEmail)}{$attribValues.useGravatarEmail->cAttribValue}{/if}" />
                            </span>
                            <span class="input-group-wrap dropdown avatar">
                                <img src="{gravatarImage email=$gravatarEmail}" title="{$oAccount->cMail}" class="img-circle" />
                            </span>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>
{/if}