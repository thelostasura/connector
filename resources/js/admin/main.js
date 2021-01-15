require('./bootstrap');

import { createApp } from "vue";
import App from "./App.vue";
import router from "./router.js";

import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

const app = createApp(App);

app.mixin({
  methods: {
    __,
    sprintf
  }
});

app.use(router);

app.use(Toast, {
  position: 'bottom-right',
  timeout: 3000,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  maxToasts: 20,
  newestOnTop: true
});

app.mount('#thelostasura-app');