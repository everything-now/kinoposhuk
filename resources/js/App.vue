<template>
    <v-app id="main">
        <v-app-bar
            app
            class="primary"
            dark
            flat
        >
            <v-spacer></v-spacer>

            <v-toolbar-title>
                <v-text-field
                    v-model="search"
                    label="Пошук"
                    single-line
                    hide-details
                    v-show="searchMode"
                    ref="search"
                    v-on:keyup.enter="filterBy('query')"
                >
                    <template slot="append">
                        <v-icon
                            v-on:click="searchMode=false; filterBy('popularity')"
                        >
                            mdi-close
                        </v-icon>
                    </template>
                </v-text-field>
                <v-icon
                    class="align-self-start mt-1 ml-auto"
                    color="secondary"
                    v-show="!searchMode"
                    v-on:click="enableSearch"
                >
                    mdi-magnify
                </v-icon>
            </v-toolbar-title>

            <v-progress-linear
                :active="loading"
                :indeterminate="loading"
                absolute
                bottom
                color="secondary"
            ></v-progress-linear>
        </v-app-bar>

        <v-main>
        <v-container style="max-width: 900px" >
            <v-row
                justify="start"
            >
                <v-col cols="7">
                    <v-chip-group
                        column
                        active-class="primary--text"
                    >
                        <v-chip
                            v-for="letter in letters"
                            :key="letter"
                            @click="filterByLetter(letter)"
                        >
                            {{ letter }}
                        </v-chip>
                    </v-chip-group>
                </v-col>
                <v-col
                    cols="5"
                    align="end"
                >
                    <v-btn-toggle
                        shaped
                    >
                        <v-btn
                            @click="filterBy('popularity')"
                        >
                            Популярні
                            <v-icon>mdi-star-outline</v-icon>
                        </v-btn>
                        <v-btn
                            @click="filterBy('release')"
                        >
                            Найновіші
                            <v-icon>mdi-calendar-text</v-icon>
                        </v-btn>
                    </v-btn-toggle>
                </v-col>
            </v-row>
            <v-row
                justify="start"
            >
                <v-flex
                    class="text-center"
                    v-show="items.length == 0"
                >
                    За вашим запитом результатів не знайдено
                </v-flex>
                <v-col
                    class="text-left"
                    cols="3"
                    v-for="item in items"
                    :key="item.id"
                    v-show="!letter || letter == item.title.charAt(0).toUpperCase()"
                >
                    <v-card
                        class="mx-auto fill-height"
                        max-width="200"
                        @click="showMovieInfo(item.id)"
                    >
                    <v-img
                        class="white--text align-end"
                        height="300px"
                        :src="item.image"
                    >
                    </v-img>

                    <v-card-title>{{ item.title }}</v-card-title>

                    <v-card-subtitle class="justify-start">{{ item.release }}</v-card-subtitle>
                    </v-card>

                </v-col>
            </v-row>
            <div class="text-center">
                <v-btn
                    v-show="pages - page > 0"
                    rounded
                    color="secondary"
                    @click="loadMore()"
                >
                    Показати більше
                    <v-icon right>mdi-download</v-icon>
                </v-btn>
                <v-pagination
                    circle
                    class="mt-4"
                    color="secondary"
                    v-model="page"
                    :length="pages"
                    :total-visible="7"
                ></v-pagination>
            </div>
        </v-container>
        </v-main>
        <v-dialog
            width="600"
            v-model="item"
            v-if="item"
        >
            <v-card>

                <v-card-title class="headline grey lighten-2">
                    {{ item.title }}
                </v-card-title>
                    <v-card-text>
                        <v-row no-gutters class="pt-4">
                        <v-col>
                            <v-img
                                class="white--text"
                                width="200px"
                                :src="item.image"
                            >
                            </v-img>
                        </v-col>
                        <v-col>
                            <p class="font-weight-regular">
                                <v-icon
                                >mdi-calendar-check</v-icon>
                                {{ item.release }}
                            </p>
                            <p class="font-weight-bold">
                                <v-icon
                                >mdi-star-half</v-icon>
                                {{ item.raiting }}
                            </p>
                            <p class="font-weight-light">{{ item.overview }}</p>
                        </v-col>
                        </v-row>
                </v-card-text>
            </v-card>
        </v-dialog>
    </v-app>
</template>


<script>
import axios from 'axios'

export default {
    data: () => ({
        loading: true,
        searchMode: false,
        loadMoreMode: false,
        filter: 'popularity',
        search: '',
        letters: 'АБВГДЕЄЖИЗІЇЙКЛМНОПРСТУФХЦЧШЩЬЮЯ',
        letter: null,
        page: 1,
        pages: 1,
        items: {},
        item: null,
        dialog: false,
    }),
    methods: {
        enableSearch () {
            this.searchMode = true;
            setTimeout(() => this.$refs.search.focus(), 30);
        },

        loadMore () {
            this.loadMoreMode = true;
            this.page++;
        },

        filterBy (method) {
            this.filter = method;
            this.page = 1;
            this.getMovies();
        },

        filterByLetter (letter) {
            if (letter == this.letter) {
                this.letter = null;
            } else {
                this.letter = letter;
            }
        },

        showMovieInfo (id) {
            this.loading = true;

            var url = '/api/movies/' + id;

            axios.get(url)
                .then(response => {
                    this.item = response.data;
                })
                .finally(() => (this.loading = false));
        },

        getMovies () {
            this.loading = true;

            var url = '/api/movies?page=' + this.page + '&filter=' + this.filter;

            if (this.filter == 'query' && this.search.length > 1) {
                url += '&query=' + this.search;
            }

            axios.get(url)
                .then(response => {
                    this.pages = response.data.total_pages;
                    this.page = response.data.page;

                    if (this.loadMoreMode) {
                        let vm = this;
                        Object.keys(response.data.results).map(function(objectKey, index) {
                            vm.items.push(response.data.results[objectKey]);
                        });
                        this.loadMoreMode = false;
                    } else {
                        this.items = response.data.results;
                    }
                })
                .finally(() => (this.loading = false));
        }
    },
    mounted() {
        this.getMovies()
    },
    watch: {
        page: function () {
            this.getMovies();
        }
    },
  }
</script>

<style lang="scss">
    .v-text-field {
        width: 400px;
    }
    .v-card__title {
        font-weight: 700 !important;
        font-size: 1rem !important;
    }
</style>
