<template>
    <form class="form m-auto" enctype="application/x-www-form-urlencoded" method="post">
        <div class="h4">
            Введите данные
        </div>
        <div class="form-group">
            <input type="text" class="form-control" placeholder="Логин" name="login" required v-model="form.name">
        </div>
        <div class="form-group">
            <input type="password" class="form-control" placeholder="Пароль" name="password" required
                   v-model="form.password">
        </div>
        <button type="submit" class="btn btn-primary" @click="login">Войти</button>
    </form>
</template>

<script>
    export default {
        name: "LoginForm",

        data() {
            return {
                form: {
                    name: '',
                    password: ''
                }
            }
        },

        methods: {

            login(event) {

                event.preventDefault();

                axios.post('/login', this.form)
                    .then(response => {

                        if (!response.data.status) {

                            alert('Неправильный логин или пароль');

                        } else {

                            window.location.href = '/system';

                        }

                    });

            }

        }
    }
</script>

<style scoped>

    .form {

        width: 500px;

    }

</style>