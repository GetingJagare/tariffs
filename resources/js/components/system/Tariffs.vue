<template>
    <div>
        <div class="mt-3 mb-3">
            <router-link to="/create-tariff">
                <button class="btn btn-primary">
                    Добавить тариф
                </button>
            </router-link>

            <!--<router-link to="/import-tariffs">
                <button class="btn btn-primary">
                    Загрузить тарифы
                </button>
            </router-link>-->

            <button class="btn btn-primary" @click.prevent="exportTariffs">
                Экспорт в YML
            </button>

            <div class="mt-3" v-if="ymlLink">
                <a :href="ymlLink" target="_blank">Ссылка на фид</a>
            </div>
        </div>
        <table class="table tariffs-table">
            <caption class="d-block">Добавленные тарифы</caption>
            <tr v-if="tariffs.length">
                <th>Название</th>
                <th>Регион</th>
                <th>Категория</th>
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
                <td>
                    {{ tariff.category.name }}
                </td>
                <td>
                    <router-link :to="'/edit-tariff/' + tariff.id" title="Редактировать тариф">
                        <span class="fa fa-pen"></span>
                    </router-link>
                </td>
                <td>
                    <a href="#" @click.prevent="deleteTariff(tariff.id)" title="Удалить тариф">
                        <span class="fa fa-trash"></span>
                    </a>
                </td>
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
    import axios from 'axios';

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
                paramsLimit: 5
            }

        },

        methods: {
            async getTariffs() {

                const response = await axios.get('/tariffs', {params: {skip: this.skip, count: this.countPerPage}});

                const tariffs = response.data.tariffs;

                this.tariffs = this.tariffs.concat(tariffs);

                this.skip += this.countPerPage;

                this.count = response.data.count;

            },

            exportTariffs() {

                this.loading = true;

                axios.get('/export')
                    .then(response => {

                        this.loading = false;

                        this.ymlLink = response.data.link;

                    });

            },

            async checkFeed() {

                const result = await axios.get('/check-feed');

                this.ymlLink = result.data.link;

            },

            deleteTariff(id) {

                if (!confirm('Вы действительно хотите удалить тариф?')) {

                    return;

                }

                this.loading = true;

                axios.post('/delete-tariff', {
                    id: id
                }).then((response) => {

                    this.loading = false;

                    if (!response.data.status) {

                        alert(response.data.error);

                    } else {

                        this.tariffs.forEach((tariff, index) => {

                            if (tariff.id === id) {

                                this.tariffs.splice(index, 1);

                            }

                        })

                    }

                });

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
