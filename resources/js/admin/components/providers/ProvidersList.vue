<template>
  <div class="flex overflow-hidden bg-white">
    <!-- Main column -->
    <div class="flex flex-col w-0 flex-1 overflow-hidden">
      <main
        class="flex-1 relative z-0 overflow-y-auto focus:outline-none"
        tabindex="0"
      >
        <!-- Page title & actions -->
        <div
          class="border-b border-gray-200 px-4 py-4 sm:flex sm:items-center sm:justify-between sm:px-6 lg:px-8"
        >
          <div class="flex-1 min-w-0">
            <h1 class="text-lg font-medium leading-6 text-gray-900 sm:truncate">
              <router-link :to="{ name: 'providers' }">
                {{ __("Provider", "asura-connector") }}
              </router-link>
            </h1>
          </div>
          <div class="mt-4 flex sm:mt-0 sm:ml-4">
            <router-link
              :to="{ name: 'provider.register' }"
              class="order-0 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:order-1 sm:ml-3"
            >
              Add New Provider
            </router-link>
          </div>
        </div>
        <router-view name="providerRegister"></router-view>
        <router-view name="providerEdit"></router-view>

        <!-- Projects table (small breakpoint and up) -->
        <div class="sm:block">
          <div
            class="align-middle inline-block min-w-full border-b border-gray-200"
          >
            <table class="min-w-full">
              <thead>
                <tr class="border-t border-gray-200">
                  <th
                    class="pl-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Site
                  </th>
                  <th
                    class="py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Provider
                  </th>
                  <th
                    class="hidden md:table-cell px-6 py-3 border-b border-gray-200 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Last updated
                  </th>
                  <th
                    class="pr-6 py-3 border-b border-gray-200 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                  ></th>
                  <th
                    class="pr-6 py-3 border-b border-gray-200 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                  ></th>
                  <th
                    class="pr-6 py-3 border-b border-gray-200 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                  ></th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <ProvidersItem
                  v-for="provider in providers"
                  :key="provider.id"
                  :id="provider.id"
                  :site-title="provider.site_title"
                  :provider="provider.provider"
                  :status="provider.status"
                  :createdAt="provider.created_at"
                  :updatedAt="provider.updated_at"
                  @delete="deleteProvider(provider.id)"
                  @edit="editProvider(provider.id)"
                />
                <!-- More project rows... -->
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import ProvidersItem from "./ProvidersItem.vue";
import ProvidersItemEdit from "./ProvidersItemEdit.vue";
import { useToast } from "vue-toastification";
import LoadingSvg from "../../UI/LoadingSvg.vue";

export default {
  components: {
    ProvidersItem,
  },
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      providers: [],
    };
  },
  beforeRouteUpdate(to, from) {
    if (to.name === "providers") {
      this.loadProvidersList();
    }
  },
  mounted() {
    this.loadProvidersList();
  },
  methods: {
    loadProvidersList() {
      axios
        .get(thelostasura.ajax_url, {
          params: {
            action: "asura_connector_list_providers",
            _wpnonce: thelostasura.nonce,
          },
        })
        .then((response) => {
          if (response.status === 200) {
            this.providers = response.data.data;
          }
        })
        .catch((error) => {
          const toastId = this.toast.error(
            __("Failed to load Providers list", "asura-connector")
          );
        });
    },
    deleteProvider(id) {
      const toastId = this.toast.info(
        __("Deleting provider ...", "asura-connector"),
        {
          timeout: false,
          icon: LoadingSvg,
        }
      );

      axios
        .post(
          thelostasura.ajax_url,
          new URLSearchParams({
            action: "asura_connector_delete_provider",
            provider_id: id,
            _wpnonce: thelostasura.nonce,
          })
        )
        .then((response) => {
          this.toast.update(toastId, {
            content: __("Provider deleted successfully.", "asura-connector"),
            options: {
              type: "success",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .catch((error) => {
          this.toast.update(toastId, {
            content: __("Failed to delete Provider.", "asura-connector"),
            options: {
              type: "error",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .then(() => {
          this.loadProvidersList();
        });
    },
    editProvider(id) {
      this.$router.push({
        name: "provider.edit",
        params: {
          providerId: id,
        },
      });
    },
  },
};
</script>