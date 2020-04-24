<template>

    <form class="mt-3 mb-3">

        <h2>
            {{ $route.params.id ? 'Редактировать тариф' : 'Добавить тариф' }}
        </h2>

        <div class="form-group">

        </div>

    </form>

</template>

<script>
    import axios from 'axios';

    export default {
        name: "EditTariff",

        data () {

            return {

                tariff: {}

            }

        },

        methods: {

            getTariff() {


                axios.get('/tariffs', {params: {id: this.$route.params.id}})
                    .then((response) => {

                        this.tariff = response.data.tariff;

                    });

            }

        }
    }
</script>

<style scoped>

</style>