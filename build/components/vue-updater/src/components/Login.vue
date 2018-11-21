<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-4">
                <div id="login_wrapper">
                    <div id="login_outer" class="card">
                        <div class="card-body">
                            <template v-if="loginErrors !== null && loginErrors.length !== 0
                                && (loginErrors.user === undefined || loginErrors.password === undefined)">
                                <div class="alert alert-danger">{{ loginErrors[Object.keys(loginErrors)[0]] }}</div>
                            </template>
                            <div class="form-group align-items-center">
                                Melden Sie sich mit Ihren Kontodaten an.
                            </div>
                            <form class="form-horizontal"
                                @keydown="form.errors.clear($event.target.name)"
                                @change="form.errors.clear($event.target.name)">
                                <input id="benutzer" type="hidden" name="adminlogin" value="1" />
                                <div class="form-group row align-items-center">
                                    <div class="input-group col-12">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text"><icon name="user"></icon></div>
                                        </div>
                                        <input :class="{'form-control': true, 'is-invalid': form.errors.has('user')}"
                                            type="text" placeholder="Benutzername" name="user" id="user_login"
                                            v-model="form.user" size="20" tabindex="10" autofocus />
                                        <span class="invalid-feedback text-center" v-if="form.errors.has('user')"
                                            v-text="form.errors.get('user')"></span>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <div class="input-group col-12">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text"><icon name="lock"></icon></div>
                                        </div>
                                        <input :class="{'form-control': true, 'is-invalid': form.errors.has('password')}"
                                            type="password" placeholder="Passwort" name="password" id="user_pass"
                                            v-model="form.password" size="20" tabindex="20" />
                                        <span class="invalid-feedback text-center" v-if="form.errors.has('password')"
                                            v-text="form.errors.get('password')"></span>
                                    </div>
                                </div>
                                <continue :form="form" :url="$getApiUrl('login')" :disable="disableLoginBtn"></continue>
                            </form>
                        </div>
                    </div>
                    <!--<div class="form-group text-center">
                        <a href="pass.php" title="Passwort vergessen"><i class="fa fa-lock"></i> Passwort vergessen?</a>
                    </div>-->
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import Form from '../helpers/class.Form';

    export default {
        name: "Login",
        data() {
            return {
                disableLoginBtn: false,
                form: new Form({
                    adminlogin: 1,
                    user:'',
                    password:'',
                    jtl_token: '',
                }),
            }
        },
        methods: {
            getJTLToken() {
                let that = this;

                axios.get(that.$getApiUrl('getJTLToken'))
                    .then(response => {
                        that.form.jtl_token  = response.data.jtl_token;
                        that.setLoginBtnDisabled(that.form.jtl_token.length === 0);
                    })
                    .catch(error => {
                        console.log('failed to get token');
                    });
            },
        },
        mounted() {
            this.setLoginBtnDisabled(this.form.jtl_token.length === 0);
            this.getJTLToken();
        }
    }
</script>

<style scoped>
    .card {
        background: #fff;
        box-shadow: 0 0 1px rgba(0,0,0,.15);
        border-radius: 0.3rem;
        padding: 15px;
    }
</style>