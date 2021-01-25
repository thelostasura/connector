import { createRouter, createWebHistory } from "vue-router";

import ProvidersList from "./components/providers/ProvidersList.vue";
import TermsList from "./components/terms/TermsList.vue";
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
      redirect: { name: 'providers' }
    },
    {
      path: '/provider',
      name: 'providers',
      components: {
        default: ProvidersList,
      },
      children: [
        {
          path: ':providerId',
          name: 'terms',
          // props: true,
          components: {
            default: TermsList
          },
          children: [
            {
              path: ':licenseId/:termSlug',
              name: 'designsets',
              // props: true,
              components: {
                default: DesignSetsList
              },
              children: []
            }
          ]
        }
      ]
    },
    
  ]
});

export default router;