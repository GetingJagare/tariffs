<template>
    <div>
        <div class="mt-3 mb-3">
            <router-link to="/create-tariff">
                <button class="btn btn-primary">
                    Добавить тариф
                </button>
            </router-link>

            <router-link to="/import-tariffs">
                <button class="btn btn-primary">
                    Загрузить тарифы
                </button>
            </router-link>

            <button class="btn btn-primary" @click.prevent="exportTariffs">
                Экспорт в YML
            </button>

            <div class="mt-3" v-if="ymlLink">
                <a :href="ymlLink" target="_blank">Ссылка на фид</a>
            </div>
        </div>
        <table class="table tariffs-table">
            <caption class="d-block">Добавленные тарифы</caption>
            <tr>
                <th>Название</th>
                <th>Регион</th>
                <th>СМС</th>
                <th>Гб</th>
                <th>Минуты</th>
                <th>Плата за сутки</th>
                <th>Стартовый баланс</th>
                <th></th>
                <th></th>
            </tr>
            <tr v-if="tariffs.length === 0">
                <td colspan="7">Нет данных</td>
            </tr>
            <tbody v-else>
            <tr v-for="tariff in tariffs">
                <td>{{ tariff.name }}</td>
                <td>{{ tariff.region.name }}</td>
                <td>{{ tariff.params.sms }}</td>
                <td>{{ tariff.params.gb }}</td>
                <td>{{ tariff.params.min }}</td>
                <td>{{ tariff.price_per_day }}</td>
                <td>{{ tariff.start_balance}}</td>
                <td></td>
                <td></td>
            </tr>
            </tbody>
        </table>

        <div class="mt-3 text-center" v-if="tariffs.length < count">
            <button class="btn btn-primary" @click.prevent="getTariffs">Показать еще</button>
        </div>

        <vue-element-loading :active="loading" is-full-screen color="#e1e1e1"></vue-element-loading>
    </div>
</template>

<script>
    export default {
        name: "Tariffs",

        data() {

            return {
                loading: false,
                countPerPage: 20,
                tariffs: [],
                skip: null,
                count: 0,
                ymlLink: null,
            }

        },

        methods: {
            async getTariffs() {

                const response = await axios.get('/tariffs', {params: {skip: this.skip, count: this.countPerPage}});

                const tariffs = response.data.tariffs;

                tariffs.forEach(tariff => {

                    tariff.params = JSON.parse(tariff.params);
                    tariff.unlimited = JSON.parse(tariff.unlimited);

                });

                this.tariffs = this.tariffs.concat(tariffs);

                this.skip += this.countPerPage;

                this.count = response.data.count;

            },

            exportTariffs() {

                this.loading = true;

                axios.get('/export')
                    .then (response => {

                        this.loading = false;

                        this.ymlLink = response.data.link;

                    });

            },

            async checkFeed() {

                const result = await axios.get('/check-feed');

                this.ymlLink = result.data.link;

            }
        },

        mounted() {

            this.getTariffs();

            this.checkFeed();

        }
    }
</script>

<style scoped>
    .tariffs-table {
        width: 100%;
    }
</style>