<template>

    <div class="mt-3 mb-3">
        <form class="tariff-form" @submit.prevent="save">

            <h2>
                {{ $route.params.id ? 'Редактировать тариф ' + (tariff.name ? tariff.name : '') : 'Добавить тариф' }}
            </h2>

            <div class="form-group">
                <label class="label">Название</label>
                <input class="form-control" v-model="tariff.name" required/>
            </div>

            <div class="form-group">
                <label class="label">Описание</label>

                <textarea class="form-control" v-model="tariff.description" rows="5"></textarea>

            </div>

            <div class="form-group">
                <label class="label">Категория</label>

                <input class="form-control" v-model="tariff.category.name" required/>

            </div>

            <div class="form-group">
                <label class="label">Регион</label>

                <input class="form-control" v-model="tariff.region.name" required/>

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
                <label class="label">Цена</label>
                <input class="form-control" v-model="tariff.price" required/>
            </div>

            <div class="form-group" v-for="field_value in tariff.field_values">
                <label class="label">
                    {{ field_value.field.name }}
                    <a href="#" title="Удалить параметр" class="ml-2">
                        <span class="fa fa-trash"></span>
                    </a>
                </label>
                <input class="form-control" v-model="field_value.value" v-if="field_value.field.type.alias === 'text'"
                       type="text"/>
                <input class="form-control" v-model="field_value.value"
                       v-else-if="field_value.field.type.alias === 'checkbox'" type="checkbox"/>
            </div>

            <div class="form-group mt-4">
                <a href="#" @click="$event.preventDefault(); addingParam = !addingParam">
                    {{ !addingParam ? 'Добавить параметр' : 'Скрыть форму' }}
                </a>

                <div v-if="addingParam">
                    <div>
                        <div class="d-flex form-group align-items-end">
                            <div class="d-flex flex-wrap mr-3 w-100">
                                <label for="param-name" style="width: 100%; font-weight: 700;">Выберите параметр</label>
                                <select class="form-control" v-if="nonAttachedParams.length" v-model="newFieldValue.id"
                                        id="param-name">
                                    <option v-for="field in nonAttachedParams" :value="field.id">
                                        {{ field.name }}
                                    </option>
                                </select>
                            </div>

                            <span class="btn btn-success" style="width: 100px;"
                                  @click="addingNewField = !addingNewField">{{ !addingNewField ? '+' : '-' }} Новый</span>
                        </div>
                        <div class="form-group" v-if="newFieldValue.id">
                            <input type="text" class="form-control" v-model="newFieldValue.value" v-if="newFieldValue.tariff_field_types_id === 'text'"/>
                            <input type="text" class="form-control" v-model="newFieldValue.value" v-if="newFieldValue.tariff_field_types_id === 'checkbox'"/>
                        </div>

                    </div>

                    <div v-if="addingNewField" class="mt-3 pt-2 pb-2 bg-light">
                        <h5>Новый параметр</h5>
                        <div class="form-group">
                            <input type="text" class="mr-3 form-control" v-model="newField.name"
                                   placeholder="Название параметра"/>
                        </div>
                        <div class="form-group">
                            <label for="param-type" style="font-weight: 700;">Выберите тип параметра</label>
                            <select class="form-control" v-model="newField.type" id="param-type">
                                <option v-for="type in fieldTypes" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                        </div>
                        <span class="btn btn-success" @click="addNewField">
                            Добавить
                        </span>
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

        data() {

            return {

                loading: false,
                fields: {},
                nonAttachedParams: [],
                fieldTypes: [],
                addingParam: false,
                addingNewField: false,
                tariff: {
                    id: null,
                    name: '',
                    category: {name: ''},
                    region: {name: ''},
                    description: '',
                    image_link: '',
                    price: 0,
                    field_values: []
                },
                newField: {
                    name: '',
                    type: null
                },
                newFieldValue: {
                    field_id: null,
                    value: null
                }

            }

        },

        methods: {

            async getTariff() {

                this.loading = true;

                axios.get('/tariffs', {params: {id: this.$route.params.id}})
                    .then((response) => {

                        let tariff = response.data.tariff;

                        tariff.image_link = tariff.image_link ? tariff.image_link.replace(/\r\n|\r|\n/, '') : '';

                        this.tariff = tariff;

                        return axios.get('/get-fields');

                    }).then((response) => {

                    this.fields = response.data;

                    if (this.tariff.field_values.length) {
                        this.tariff.field_values.forEach((field_value) => {

                            if (this.fields.indexOf(field_value.field.id) < 0) {
                                this.nonAttachedParams.push({id: field_value.field.id, name: field_value.field.name});
                            }

                        });
                    } else {

                        this.nonAttachedParams = this.fields.slice();

                    }

                    this.newFieldValue.tariffs_id = this

                    return axios.get('/get-field-types');

                }).then((response) => {

                    this.fieldTypes = response.data;

                    this.loading = false;

                });

            },

            async save() {

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

            },

            async addNewField() {

                this.loading = true;

                axios.post('/add-field', {field: this.newField})
                    .then((response) => {

                        this.loading = false;

                        if (response.data.error) {

                            alert(response.data.error);

                            return;

                        }

                        this.nonAttachedParams.push(response.data);

                        this.newField = {name: '', type: null};
                        this.newFieldValue.id = response.data.id;

                        this.addingNewField = false;

                    });

            }

        },

        async mounted() {

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
