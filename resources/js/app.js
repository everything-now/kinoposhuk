import Vue from 'vue'
import App from './App.vue'
import Vuetify from 'vuetify'

Vue.use(Vuetify)

const vuetify = new Vuetify({
    theme: {
      themes: {
        light: {
          primary: '#032541',
          secondary: '#01b4e4',
        },
      },
    },
  })


new Vue({
    el: '#app',
    vuetify: vuetify,
    render: h => h(App),
});
