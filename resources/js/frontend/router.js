import { createRouter, createWebHistory } from "vue-router";

import ProvidersList from "./components/providers/ProvidersList.vue";
import DesignSetsList from "./components/designsets/DesignSetsList.vue";

const router = createRouter({
  history: createWebHistory('#/'),
  scrollBehavior(_, _2, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    }
    return { left: 0, top: 0 }
  },
  routes: [
    {
      path: '/',
      redirect: { name: 'providers-list' }
    },
    {
      path: '/provider',
      name: 'providers-list',
      components: {
        default: ProvidersList,
      },
      children: [
        {
          path: ':providerId',
          name: 'provider',
          props: true,
          components: {
            default: DesignSetsList
          },
          // children: [
          //   {
          //     path: ':designSetSlug/',
          //     name: 'designset-slug',
          //     props: true,
          //     components: {
          //       default: DesignSetsList
          //     },
          //     children: []
          //   }
          // ]
        }
      ]
    },
    
  ]
});

export default router;