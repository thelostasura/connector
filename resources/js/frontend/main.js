require('./bootstrap');

import { createApp } from "vue";
import App from "./App.vue";

const app = createApp(App);

app.mixin({
  methods: {
    __,
    sprintf,
  },
});

app.mount('#thelostasura-app');








