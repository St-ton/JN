{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    <h1>{lang key='forgotPassword' section='global'}</h1>
    
    {include file='snippets/extension.tpl'}
    {if !empty($cFehler)}
        <div class="alert alert-danger">{$cFehler}</div>
    {/if}
    {if $step === 'formular'}
        {if empty($hinweis)}
            <div class="alert alert-info">{lang key='forgotPasswordDesc' section='forgot password'}</div>
        {else}
            <div class="alert alert-danger">{$hinweis}</div>
        {/if}
        <div class="row">
            <div class="col-xs-12 col-md-8 col-md-offset-2">
                {block name='password-reset-form'}
                <div class="panel-wrap">
                    {block name='password-reset-form-body'}
                    <form id="passwort_vergessen" action="{get_static_route id='pass.php'}{if $bExclusive === true}?exclusive_content=1{/if}" method="post" class="evo-validate">
                        {$jtl_token}
                        <fieldset>
                            {include file='snippets/form_group_simple.tpl'
                                options=[
                                    'email', 'email', 'email', null,
                                    {lang key='emailadress'}, true
                                ]
                            }
                            <div class="form-group">
                                {if $bExclusive === true}
                                  <input type="hidden" name="exclusive_content" value="1" />
                                {/if}
                                <input type="hidden" name="passwort_vergessen" value="1" />
                                <input type="submit" class="btn btn-primary btn-block submit submit_once" value="{lang key='createNewPassword' section='forgot password'}" />
                            </div>
                        </fieldset>
                    </form>
                    {/block}
                </div>
                {/block}
            </div>
        </div>
    {elseif $step === 'confirm'}
        {if $hinweis}
            <div class="alert alert-danger">{$hinweis}</div>
        {/if}
        <div class="row">
            <div class="col-xs-12 col-md-8 col-md-offset-2">
                {block name='password-reset-confirm'}
                <div class="well">
                    <div class="panel panel-default">
                        <div class="panel-heading"><h3 class="panel-title">{block name='password-reset-confirm-title'}{lang key='customerInformation' section='global'}{/block}</h3></div>
                        <div class="panel-body">
                            {block name='password-reset-confirm-body'}
                            <form id="passwort_vergessen" action="{get_static_route id='pass.php'}{if $bExclusive === true}?exclusive_content=1{/if}" method="post" class="evo-validate">
                                {$jtl_token}
                                <fieldset>
                                    {include file='snippets/form_group_simple.tpl'
                                        options=[
                                            "password", "pw_new", "pw_new", null,
                                            {lang key='password' section='account data'}, true
                                        ]
                                    }
                                    {include file='snippets/form_group_simple.tpl'
                                        options=[
                                            "password", "pw_new_confirm", "pw_new_confirm", null,
                                            {lang key='passwordRepeat' section='account data'}, true
                                        ]
                                    }
                                    <div class="form-group">
                                        {if $bExclusive === true}
                                            <input type="hidden" name="exclusive_content" value="1">
                                        {/if}
                                        <input type="hidden" name="fpwh" value="{$fpwh}">
                                        <input type="submit" class="btn btn-primary btn-block submit submit_once" value="{lang key='createNewPassword' section='forgot password'}">
                                    </div>
                                </fieldset>
                            </form>
                            {/block}
                        </div>
                    </div>
                </div>
                {/block}
            </div>
        </div>
    {else}
        <div class="alert alert-success">{lang key='newPasswortWasGenerated' section='forgot password'}</div>
    {/if}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
