<template>
    <div>
        <div class="mt-3 mb-3">
            <router-link to="/import-tariffs">
                <button class="btn btn-primary">
                    Загрузить тарифы
                </button>
            </router-link>

            <router-link to="/export-tariffs">
                <button class="btn btn-primary">
                    Экспорт в YML
                </button>
            </router-link>
        </div>
        <table class="table tariffs-table">
            <caption class="d-block">Добавленные тарифы</caption>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Регион</th>
            </tr>
            <tr v-if="!tariffs.length">
                <td colspan="3">Нет данных</td>
            </tr>
            <tbody v-else>
            <tr v-for="tariff in tariffs">
                <td>{{ tariff.id }}</td>
                <td>{{ tariff.name }}</td>
                <td>{{ tariff.region.name }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        name: "Tariffs",

        data() {

            return {
                countPerPage: 20,
                tariffs: [],
                skip: null
            }

        },

        methods: {
            async getTariffs() {

                this.tariffs = await axios.get('/tariffs', {skip: this.skip, count: this.countPerPage});

                this.skip += this.countPerPage;

            }
        },

        mounted() {

            this.getTariffs();

        }
    }
</script>

<style scoped>
    .tariffs-table {
        width: 100%;
    }
</style>