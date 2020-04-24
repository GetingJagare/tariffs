require('./bootstrap');

import Vue from 'vue';
import VueElementLoading from 'vue-element-loading';
import VueRouter from 'vue-router';
import LoginForm from './components/LoginForm.vue';
import LogoutLink from './components/LogoutLink.vue';

Vue.component('login-form', LoginForm);
Vue.component('logout-link', LogoutLink);
Vue.component('vue-element-loading', VueElementLoading);

Vue.use(VueRouter);

import Tariffs from './components/system/Tariffs.vue';
import ImportTariffs from './components/system/ImportTariffs.vue';
import EditTariff from './components/system/EditTariff.vue';

const router = new VueRouter({
    routes: [
        {
            path: '/',
            component: {
                template: '<div class="text-center mt-2"><h1>Главная</h1></div>'
            }
        },
        {
            path: '/tariffs',
            component: Tariffs
        },
        {
            path: '/import-tariffs',
            component: ImportTariffs
        },
        {
            path: '/create-tariff',
            component: EditTariff,
        },
        {
            path: '/edit-tariff/:id',
            component: EditTariff,
        },
    ]
});

const app = new Vue({
    el: '#app',
    router
});
