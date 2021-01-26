<template>
  <div class="p-8 bg-gray-50">
    <div class="sm:mt-0">
      <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
          <div class="px-4 sm:px-0">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
              {{ providerEditHeader }}
            </h3>
            <p class="mt-1 text-sm leading-5 text-gray-600">
              {{
                __(
                  "Edit your provider config. The provider allows you to make connection to access design set that managed using thelostasura.com plugin",
                  "asura-connector"
                )
              }}
            </p>
          </div>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
          <div class="shadow sm:rounded-md">
            <div
              v-if="form.errors.length >= 1"
              class="rounded-md bg-red-50 p-4"
            >
              <div class="flex">
                <div class="flex-shrink-0">
                  <!-- Heroicon name: x-circle -->
                  <svg
                    class="h-5 w-5 text-red-400"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm leading-5 font-medium text-red-800">
                    There were {{ form.errors.length }} error(s) with your
                    submission
                  </h3>
                  <div class="mt-2 text-sm leading-5 text-red-700">
                    <ul class="list-disc pl-5">
                      <li v-for="error in form.errors" :key="error">
                        {{ error.message }}
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <div class="px-4 py-5 bg-white sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6 sm:col-span-2">
                  <label
                    for="site_title"
                    class="block text-sm font-medium leading-5 text-gray-700"
                    >Site title <span class="text-red-800">*</span></label
                  >
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <input
                      type="text"
                      v-model="form.site_title"
                      id="site_title"
                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm sm:leading-5 border-gray-300 rounded-md placeholder-gray-500 placeholder-opacity-50"
                      placeholder="-"
                    />
                  </div>
                </div>
                <div class="col-span-6 sm:col-span-2">
                  <label
                    for="provider"
                    class="block text-sm font-medium leading-5 text-gray-700"
                    >Provider <span class="text-red-800">*</span></label
                  >
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <input
                      type="text"
                      v-model="form.provider"
                      id="provider"
                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm sm:leading-5 border-gray-300 rounded-md placeholder-gray-500 placeholder-opacity-50"
                      placeholder="-"
                    />
                  </div>
                </div>
                <div class="col-span-6 sm:col-span-1">
                  <label
                    for="endpoint"
                    class="block text-sm font-medium leading-5 text-gray-700"
                    >Endpoint <span class="text-red-800">*</span></label
                  >
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <input
                      type="text"
                      v-model="form.endpoint"
                      id="endpoint"
                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm sm:leading-5 border-gray-300 rounded-md placeholder-gray-500 placeholder-opacity-50"
                      placeholder="-"
                    />
                  </div>
                </div>
                <div class="col-span-6 sm:col-span-1">
                  <label
                    for="version"
                    class="block text-sm font-medium leading-5 text-gray-700"
                    >Version <span class="text-red-800">*</span></label
                  >
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <input
                      type="text"
                      v-model="form.version"
                      id="version"
                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm sm:leading-5 border-gray-300 rounded-md placeholder-gray-500 placeholder-opacity-50"
                      placeholder="-"
                    />
                  </div>
                </div>
                <div class="col-span-6 sm:col-span-3">
                  <label
                    for="api_key"
                    class="block text-sm font-medium leading-5 text-gray-700"
                    >Key <span class="text-red-800">*</span></label
                  >
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <input
                      type="password"
                      v-model="form.api_key"
                      id="api_key"
                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm sm:leading-5 border-gray-300 rounded-md placeholder-gray-500 placeholder-opacity-50"
                      placeholder="-"
                    />
                  </div>
                </div>
                <div class="col-span-6 sm:col-span-3">
                  <label
                    for="api_secret"
                    class="block text-sm font-medium leading-5 text-gray-700"
                    >Secret <span class="text-red-800">*</span></label
                  >
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <input
                      type="password"
                      v-model="form.api_secret"
                      id="api_secret"
                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm sm:leading-5 border-gray-300 rounded-md placeholder-gray-500 placeholder-opacity-50"
                      placeholder="-"
                    />
                  </div>
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
              <router-link
                :to="{ name: 'providers' }"
                type="button"
                class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:order-0 sm:ml-0"
              >
                Cancel
              </router-link>
              <button
                @click="doSave"
                type="button"
                class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:order-1 sm:ml-3"
              >
                Save
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useToast } from "vue-toastification";
import LoadingSvg from "../../UI/LoadingSvg.vue";

export default {
  setup() {
    const toast = useToast();
    return { toast };
  },
  props: {
    providerId: String,
  },
  data() {
    return {
      form: {
        provider: "",
        site_title: "",
        api_key: "",
        api_secret: "",
        endpoint: "",
        version: "",
        errors: [],
      },
    };
  },
  computed: {
    providerEditHeader() {
      return sprintf(
        /* translators: %s: provider id  */
        __("Edit Provider #%s", "asura-connector"),
        this.providerId
      );
    },
  },
  methods: {
    doSave() {
      this.form.errors = [];

      const toastId = this.toast.info(
        __("Updating the Provider ...", "asura-connector"),
        {
          timeout: false,
          icon: LoadingSvg,
        }
      );

      axios
        .post(
          thelostasura.ajax_url,
          new URLSearchParams({
            action: "asura_connector_edit_provider",
            _wpnonce: thelostasura.nonce,
            provider_id: this.providerId,
            provider: this.form.provider,
            site_title: this.form.site_title,
            api_key: this.form.api_key,
            api_secret: this.form.api_secret,
            endpoint: this.form.endpoint,
            version: this.form.version,
          })
        )
        .then((response) => {
          this.toast.update(toastId, {
            content: __("Provider updated successfully.", "asura-connector"),
            options: {
              type: "success",
              icon: true,
              timeout: 5000,
            },
          });

          this.$router.push({ name: "providers" });
        })
        .catch((error) => {
          this.toast.update(toastId, {
            content: __("Failed to update the Provider.", "asura-connector"),
            options: {
              type: "error",
              icon: true,
              timeout: 5000,
            },
          });

          error.response.data.data.forEach((element) => {
            this.form.errors.push({ message: element.message });
          });
        })
        .then(() => {});
    },
    loadProvider(id) {
      axios
        .get(thelostasura.ajax_url, {
          params: {
            action: "asura_connector_get_provider",
            _wpnonce: thelostasura.nonce,
            provider_id: id ? id : this.providerId,
          },
        })
        .then((response) => {
          if (response.status === 200) {
            const provider = response.data.data;

            this.form.provider = provider.provider;
            this.form.site_title = provider.site_title;
            this.form.api_key = provider.api_key;
            this.form.api_secret = provider.api_secret;
            this.form.endpoint = provider.endpoint;
            this.form.version = provider.version;
          }
        })
        .catch((error) => {
          const toastId = this.toast.error(
            __("Failed to load the provider", "asura-connector")
          );
        });
    },
  },
  beforeRouteUpdate(to, from) {
    this.loadProvider(to.params.providerId);
  },
  mounted() {
    this.loadProvider();
  },
  created() {},
};
</script>