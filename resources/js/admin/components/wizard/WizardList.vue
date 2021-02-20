<template>
  <!-- This example requires Tailwind CSS v2.0+ -->
  <div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
      <h3 class="flex text-lg leading-6 font-medium text-gray-900">
        <router-link :to="{ name: 'providers' }" class="flex">
          {{ templateHeaderHome }}
          <svg
            class="w-5 h-5 mt-1 mx-2"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 5l7 7-7 7"
            />
          </svg>
        </router-link>
        {{ __("Install Wizard", "asura-connector") }}
      </h3>
      <p class="mt-1 max-w-2xl text-sm text-gray-500">
        {{
          __(
            "Install a design set from this provider.",
            "asura-connector"
          )
        }}
      </p>
    </div>
    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
      <dl class="sm:divide-y sm:divide-gray-200">
        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
          <dt class="text-sm font-medium text-gray-500">Provider</dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            {{ provider.site_title }} — <a :href="provider.provider" target="_blank"> {{ provider.provider }} </a>
          </dd>
        </div>
        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
          <dt class="text-sm font-medium text-gray-500">License Keys</dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <ul
              class="border border-gray-200 rounded-md divide-y divide-gray-200"
            >
              <WizardItem
                v-for="license in licenses"
                :key="license.id"
                :id="license.id"
                :license-key="license.license"
                @delete="deleteLicense(license.id)"
              />

              <li
                class="pl-3 pr-4 py-3 flex items-center justify-between text-sm"
              >
                <form @submit.prevent="doSave" class="flex-1 flex w-full">
                  <div class="w-0 flex-1 flex items-center">
                    Add License key:
                    <div
                      class="ml-2 mr-10 flex-1 w-0 relative rounded-md shadow-sm"
                    >
                      <div
                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                      >
                        <!-- Heroicon name: key -->
                        <svg
                          class="mt-1 h-5 w-5 text-gray-400"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 24 24"
                          fill="none"
                          aria-hidden="true"
                          stroke="currentColor"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"
                          />
                        </svg>
                      </div>
                      <input
                        v-model="form.license_key"
                        type="text"
                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm sm:leading-5 border-gray-300 rounded-md placeholder-gray-500 placeholder-opacity-50"
                      />
                    </div>
                  </div>
                  <div class="ml-4 flex-shrink-0">
                    <button
                      type="submit"
                      class="flex px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:order-1 sm:ml-3"
                    >
                      <svg
                        class="h-5 w-5"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                        />
                      </svg>
                      {{ __("Add", "asura-connector") }}
                    </button>
                  </div>
                </form>
              </li>
            </ul>
            <div
              v-if="form.errors.length >= 1"
              class="rounded-md bg-red-50 p-4 mt-2"
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
                    {{ errorMessageHeader }}
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
          </dd>
        </div>
      </dl>
    </div>
  </div>
</template>

<script>
import WizardItem from "./WizardItem.vue";
import { useToast } from "vue-toastification";
import LoadingSvg from "../../UI/LoadingSvg.vue";

export default {
  components: {
    WizardItem,
  },
  setup() {
    const toast = useToast();
    return { toast };
  },
  props: {
    providerId: String,
  },
  computed: {
    templateHeaderHome() {
      return sprintf(
        /* translators: 1: provider id  */
        __("Provider #%s", "asura-connector"),
        this.provider_id
      );
    },
    errorMessageHeader() {
      return sprintf(
        /* translators: %s: number of error(s)  */
        __("There were %s error(s) with your submission", "asura-connector"),
        this.form.errors.length
      );
    },
  },
  data() {
    return {
      provider_id: null,
      licenses: [],
      form: {
        license_key: "",
        errors: [],
      },
      provider: {
        provider: "",
        site_title: "",
        endpoint: "",
        version: "",
      },
    };
  },
  beforeRouteUpdate(to, from) {
    if (to.name === "licenses") {
      this.provider_id = to.params.providerId;
      this.loadProvider();
      this.loadLicensesList();
    }
  },
  mounted() {
    this.provider_id = this.providerId;
    this.loadProvider();
    this.loadLicensesList();
  },
  methods: {
    loadLicensesList(id) {
      axios
        .get(thelostasura.ajax_url, {
          params: {
            action: "asura_connector_list_licenses",
            _wpnonce: thelostasura.nonce,
            provider_id: id ? id : this.provider_id,
          },
        })
        .then((response) => {
          if (response.status === 200) {
            this.licenses = response.data.data;
          }
        })
        .catch((error) => {
          const toastId = this.toast.error(
            __("Failed to load Licenses list", "asura-connector")
          );
        });
    },
    loadProvider(id) {
      axios
        .get(thelostasura.ajax_url, {
          params: {
            action: "asura_connector_get_provider",
            _wpnonce: thelostasura.nonce,
            provider_id: id ? id : this.provider_id,
          },
        })
        .then((response) => {
          if (response.status === 200) {
            const provider = response.data.data;

            this.provider.provider = provider.provider;
            this.provider.site_title = provider.site_title;
            this.provider.endpoint = provider.endpoint;
            this.provider.version = provider.version;
          }
        })
        .catch((error) => {
          console.log(error);
          const toastId = this.toast.error(
            __("Failed to load the provider", "asura-connector")
          );
        });
    },
    deleteLicense(id) {
      const toastId = this.toast.info(
        __("Deleting license ...", "asura-connector"),
        {
          timeout: false,
          icon: LoadingSvg,
        }
      );

      axios
        .post(
          thelostasura.ajax_url,
          new URLSearchParams({
            action: "asura_connector_delete_license",
            license_id: id,
            provider_id: this.provider_id,
            _wpnonce: thelostasura.nonce,
          })
        )
        .then((response) => {
          this.toast.update(toastId, {
            content: __("License deleted successfully.", "asura-connector"),
            options: {
              type: "success",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .catch((error) => {
          this.toast.update(toastId, {
            content: __("Failed to delete License.", "asura-connector"),
            options: {
              type: "error",
              icon: true,
              timeout: 5000,
            },
          });

          for (const prop in error.response.data.data) {
            error.response.data.data.forEach((element) => {
              this.form.errors.push({ message: element.message });
            });
          }
        })
        .then(() => {
          this.loadLicensesList();
        });
    },
    doSave() {
      this.form.errors = [];

      const toastId = this.toast.info(
        __("Adding new license key ...", "asura-connector"),
        {
          timeout: false,
          icon: LoadingSvg,
        }
      );

      axios
        .post(
          thelostasura.ajax_url,
          new URLSearchParams({
            action: "asura_connector_add_license",
            license_key: this.form.license_key,
            provider_id: this.provider_id,
            _wpnonce: thelostasura.nonce,
          })
        )
        .then((response) => {
          this.toast.update(toastId, {
            content: __(
              "New license key added successfully.",
              "asura-connector"
            ),
            options: {
              type: "success",
              icon: true,
              timeout: 5000,
            },
          });

          this.loadLicensesList();
          this.form.license_key = "";
        })
        .catch((error) => {
          this.toast.update(toastId, {
            content: __("Failed to add new license key.", "asura-connector"),
            options: {
              type: "error",
              icon: true,
              timeout: 5000,
            },
          });

          for (const prop in error.response.data.data) {
            error.response.data.data.forEach((element) => {
              this.form.errors.push({ message: element.message });
            });
          }
        })
        .then(() => {});
    },
  },
};
</script>
