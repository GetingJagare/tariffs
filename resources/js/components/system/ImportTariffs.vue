<template>
    <div class="mt-3">
        <h2>
            Загрузить тарифы
        </h2>
        <form class="form mt-3 mb-3" enctype="multipart/form-data" method="post" ref="tariffForm">

            <div class="form-group">

                <div class="form-group">
                    <input type="file" name="file" required id="tariffs-file"/>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" @click="importTariffs">Загрузить</button>
        </form>

        <vue-element-loading :active="loading" is-full-screen color="#e1e1e1"></vue-element-loading>
    </div>
</template>

<script>
    export default {
        name: "ImportTariffs",

        data() {

            return {
                loading: false
            }

        },

        methods: {

            async importTariffs(event) {

                event.preventDefault();

                this.loading = true;

                const formData = new FormData(this.$refs.tariffForm);

                const status = await axios.post('/import-tariffs', formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }
                ).then(response => {

                    this.loading = false;

                }).catch(error => {

                    this.loading = false;

                    alert('Произошла ошибка импорта');

                });

            }

        },
    }
</script>

<style scoped>

</style>