<template>

    <div class="mt-3 mb-3">
        <form class="tariff-form" @submit.prevent="save">

            <h2>
                {{ $route.params.id ? 'Редактировать тариф ' + (tariff.name ? tariff.name : '') : 'Добавить тариф' }}
            </h2>

            <div class="form-group">
                <label class="label">Название</label>
                <input class="form-control" v-model="tariff.name" required />
            </div>

            <div class="form-group">
                <label class="label">Описание</label>

                <textarea class="form-control" v-model="tariff.description" rows="5"></textarea>

            </div>

            <div class="form-group">
                <label class="label">Категория</label>

                <input class="form-control" v-model="tariff.category_name" required />

            </div>

            <div class="form-group">
                <label class="label">Регион</label>

                <input class="form-control" v-model="tariff.region_name" required />

            </div>

            <div class="form-group">
                <label class="label">Изображение</label>

                <div class="mt-3 mb-3" v-if="tariff.image_link.length > 0">
                    <a :href="tariff.image_link" target="_blank">
                        <img :src="tariff.image_link" class="tariff-image"/>
                    </a>
                </div>

                <textarea class="form-control" v-model="tariff.image_link" rows="2"
                          placeholder="Ссылка на изображение"></textarea>

            </div>

            <div class="form-group">
                <label class="label">Минуты</label>
                <input class="form-control" v-model="tariff.params.min" />
            </div>

            <div class="form-group">
                <label class="label">Интернет</label>
                <input class="form-control" v-model="tariff.params.gb" />
            </div>

            <div class="form-group">
                <label class="label">СМС</label>
                <input class="form-control" v-model="tariff.params.sms" />
            </div>

            <div class="form-group">
                <label class="label">Цена</label>
                <input class="form-control" v-model="tariff.price" required />
            </div>

            <div class="form-group">
                <label class="label">Абонентская плата за сутки</label>
                <input class="form-control" v-model="tariff.price_per_day" />
            </div>

            <div class="form-group">
                <label class="label">Стартовый баланс</label>
                <input class="form-control" v-model="tariff.start_balance" />
            </div>

            <div class="form-group">
                <label class="label">Безлимит</label>

                <div class="d-flex justify-content-between">

                    <div class="d-flex align-items-center">
                        <input type="checkbox" class="form-control" v-model="tariff.unlimited.whatsapp" id="unlim-whatsapp"/>
                        <label for="unlim-whatsapp">На WhatsApp</label>
                    </div>

                    <div class="d-flex align-items-center">
                        <input type="checkbox" class="form-control" v-model="tariff.unlimited.viber" id="unlim-viber" />
                        <label for="unlim-viber">На Viber</label>
                    </div>

                    <div class="d-flex align-items-center">
                        <input type="checkbox" class="form-control" v-model="tariff.unlimited.skype" id="unlim-skype" />
                        <label for="unlim-skype">На Skype</label>
                    </div>

                    <div class="d-flex align-items-center">
                        <input type="checkbox" class="form-control" v-model="tariff.unlimited.network" id="unlim-network" />
                        <label for="unlim-network">Внутри сети</label>
                    </div>

                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary">Сохранить</button>
            </div>

        </form>

        <vue-element-loading :active="loading" is-full-screen color="#e1e1e1"></vue-element-loading>
    </div>

</template>

<script>
    import axios from 'axios';

    export default {
        name: "EditTariff",

        data () {

            return {

                loading: false,
                tariff: {
                    id: null,
                    name: '',
                    category_name: '',
                    region_name: '',
                    description: '',
                    params: {
                        min: '',
                        gb: '',
                        sms: ''
                    },
                    image_link: '',
                    price: 0,
                    price_per_day: 0,
                    start_balance: 0,
                    unlimited: {
                        whatsapp: false,
                        viber: false,
                        skype: false,
                        network: false,
                    }
                }

            }

        },

        methods: {

            getTariff() {

                this.loading = true;

                axios.get('/tariffs', {params: {id: this.$route.params.id}})
                    .then((response) => {

                        this.loading = false;

                        let tariff = response.data.tariff;

                        tariff.image_link = tariff.image_link ? tariff.image_link.replace(/\r\n|\r|\n/, '') : '';
                        tariff.params = JSON.parse(tariff.params);
                        tariff.unlimited = JSON.parse(tariff.unlimited);

                        for (let key in tariff.unlimited) {

                            tariff.unlimited[key] = tariff.unlimited[key] == '1';

                        }

                        this.tariff = tariff;

                    });

            },

            save() {

                this.loading = true;

                axios.post('/save-tariff', {tariff: this.tariff})
                    .then((response) => {

                        this.loading = false;

                        if (!response.data.status) {

                            alert(response.data.error);

                        } else if (!this.tariff.id) {

                            window.scrollTo(0, 0);

                            this.tariff.id = response.data.id;

                            this.$router.push('/edit-tariff/' + response.data.id);

                        }

                    });

            }

        },

        mounted() {

            if (this.$route.params.id) {

                this.getTariff();

            }

        }
    }
</script>

<style scoped>

    .tariff-form .label {
        font-weight: 700;
    }

    .tariff-image {
        max-width: 150px;
    }

    input[type="checkbox"] {
        width: 16px;
        height: 16px;
        margin-right: 10px;
    }

    input[type="checkbox"] + label {
        margin-bottom: 0;
    }

</style>