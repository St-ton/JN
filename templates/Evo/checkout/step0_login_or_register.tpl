{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

<div class="well">
    <form method="post" action="bestellvorgang.php" class="form" id="order_register_or_login">
       {if $hinweis}
            <div class="alert alert-danger">{$hinweis}</div>
       {/if}
        <div class="row">
            {* Create new Account *}
            <div class="col-sm-12 col-md-6">
                {block name="checkout-new-account"}
                <div class="panel panel-default" id="order_choose_order_type">
                    <div class="panel-heading">
                        <h3 class="panel-title">{block name="checkout-new-account-title"}{lang key="createNewAccount" section="account data"}{/block}</h3>
                    </div>
                    <div class="panel-body">
                        {block name="checkout-new-account-body"}
                        <p>{lang key="createNewAccountDesc" section="checkout"}</p>
                        <a class="btn btn-primary btn-block" href="registrieren.php?checkout=1" class="submit">
                            {lang key="createNewAccount" section="account data"}
                        </a>
                        {if $Einstellungen.kaufabwicklung.bestellvorgang_unregistriert === 'Y'}
                            <hr>
                            <p>{lang key="orderWithoutRegistrationDesc" section="checkout"}</p>
                            <a class="btn btn-default btn-block" href="bestellvorgang.php?unreg=1" class="submit">{lang key="orderUnregistered" section="checkout"}</a>
                        {/if}
                        {/block}
                    </div>
                </div>
                {/block}
            </div>

            {* Login form *}
            <div class="col-sm-12 col-md-6">
                {block name="checkout-login"}
                <div class="panel panel-default " id="order_customer_login">
                    <div class="panel-heading">
                        <h3 class="panel-title">{block name="checkout-login-title"}{lang key="loginForRegisteredCustomers" section="checkout"}{/block}</h3>
                    </div>
                    <div class="panel-body">
                        {block name="checkout-login-body"}
                        <fieldset>
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="form-group required">
                                        <label class="control-label" for="email">{lang key="emailadress" section="global"}</label>
                                        <input
                                        class="form-control"
                                        type="text"
                                        name="userLogin"
                                        id="email"
                                        placeholder="{lang key="emailadress" section="global"}"
                                        required
                                        >
                                    </div>
                                </div>
                                <div class="col-xs-12">
                                    <div class="form-group required">
                                        <label class="control-label" for="password">{lang key="password" section="account data"}</label>
                                        <input
                                        class="form-control"
                                        type="password"
                                        name="passLogin"
                                        id="password"
                                        placeholder="{lang key="password" section="account data"}"
                                        required
                                        >
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="form-group">
                                        {$jtl_token}
                                        <input type="hidden" name="login" value="1" />
                                        <input type="hidden" name="wk" value="1" />
                                        <input type="submit" class="submit btn btn-primary btn-block" value="{lang key="login" section="checkout"}" />
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 register-or-resetpw">
                                    <small>
                                    <a class="resetpw  pull-right" href="pass.php?exclusive_content=1" onclick="window.open(this.href,this.target,'width=640,height=430'); return false;"><span class="fa fa-question-circle"></span> {lang key="forgotPassword" section="global"}</a>
                                    </small>
                                </div>
                            </div>
                        </fieldset>
                        {/block}
                    </div>
                </div>
                {/block}
            </div>
        </div>{* /row *}
    </form>
</div>{* /well *}