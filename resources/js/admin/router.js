import { createRouter, createWebHistory } from "vue-router";

import ProvidersList from "./components/providers/ProvidersList.vue";
import ProvidersRegister from "./components/providers/ProvidersRegister.vue";
import ProvidersItemEdit from "./components/providers/ProvidersItemEdit.vue";
import LicensesList from "./components/licenses/LicensesList.vue";
import WizardList from "./components/wizard/WizardList.vue";

const router = createRouter({
  history: createWebHistory('/wp-admin/admin.php?page=asura-connector#/'),
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
          path: 'register',
          name: 'provider.register',
          components: {
            providerRegister: ProvidersRegister
          }
        },
        {
          path: ':providerId/edit',
          name: 'provider.edit',
          components: {
            providerEdit: ProvidersItemEdit
          },
          props: true
        }
      ]
    },
    {
      path: '/provider/:providerId/licenses',
      name: 'licenses',
      props: true,
      components: {
        default: LicensesList
      }
    },
    {
      path: '/provider/:providerId/wizard',
      name: 'wizard',
      props: true,
      components: {
        default: WizardList
      }
    }
  ]
});

export default router;