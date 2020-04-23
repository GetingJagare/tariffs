<template>

    <a class="ml-2 d-inline-block" href="/logout" @click="logout">Выйти</a>

</template>

<script>
    export default {
        name: "LogoutLink",

        methods: {

            logout (event) {

                event.preventDefault();

                axios.post('/logout')
                    .then(response => {

                        if (response.data.status) {

                            window.location.href = '/login';

                        }

                    });

            }

        }
    }
</script>

<style scoped>

</style>