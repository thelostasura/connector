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
        <!-- Heroicon name: breaker -->
        <svg 
          class="mx-3 h-7 w-h-7 text-gray-400 group-hover:text-gray-500"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
        </svg>
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
          <dt class="text-sm font-medium text-gray-500">Overwrite all Oxygen's settings?</dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
              <span class="mr-4" :class="!Boolean(form.overwrite) ? 'p-1 rounded-sm text-gray-800 font-semibold bg-gray-200 dark:bg-gray-300' : ''">
                {{ __("Keep & disable", "asura-connector") }}
              </span>
              
              <!-- On: "bg-purple-600", Off: "bg-gray-200" -->
              <button @click="form.overwrite = !Boolean(form.overwrite)" :class="Boolean(form.overwrite) ? 'bg-purple-600' : 'bg-gray-200 dark:bg-gray-300'" type="button" class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 dark:focus:ring-offset-gray-900 sm:ml-auto">
                  <span class="sr-only">⚠ Overwrite</span>
                  <!-- On: "translate-x-5", Off: "translate-x-0" -->
                  <span :class="Boolean(form.overwrite) ? 'translate-x-5' : 'translate-x-0'" class="inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
              </button>
                
              <span class="ml-4" :class="Boolean(form.overwrite) ? 'p-1 rounded-sm text-red-700 font-semibold bg-gray-200 dark:bg-gray-300' : ''">
                {{ __("⚠ Overwrite", "asura-connector") }}
              </span>
          </dd>
        </div>
        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
          <dt class="text-sm font-medium text-gray-500"> {{ __("Design sets", "asura-connector") }} </dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <ul
              class="border border-gray-200 rounded-md divide-y divide-gray-200"
            >
              <WizardItem
                v-for="term in terms"
                :key="term.slug"
                :slug="term.slug"
                :name="term.name"
                @install="doInstall(term.provider_id, term.license_id, term.slug)"
              />
            </ul>
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
    const toastId = null;
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
      terms: [],
      form: {
        overwrite: false,
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
    if (to.name === "wizard") {
      this.provider_id = to.params.providerId;
      this.loadProvider();
      this.loadTermsList();
    }
  },
  mounted() {
    this.provider_id = this.providerId;
    this.loadProvider();
    this.loadTermsList();
  },
  methods: {
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
          const localToast = this.toast.error(
            __("Failed to load the provider", "asura-connector")
          );
        });
    },
    loadTermsList(id) {
      axios
        .get(thelostasura.ajax_url, {
          params: {
            action: "asura_connector_list_terms",
            _wpnonce: thelostasura.nonce,
            provider_id: id ? id : this.provider_id,
          },
        })
        .then((response) => {
          if (response.status === 200) {
            this.terms = response.data.data;
          }
        })
        .catch((error) => {
          const localToast = this.toast.error(
            __("Failed to load Design sets list", "asura-connector")
          );
        });
    },

    doInstall(id, license_id, term_slug) {
      this.toastId = this.toast.info(
        sprintf(
          /* translators: %s: term_slug  */
          __("Installing designset: %s", "asura-connector"),
          term_slug
        ),
        {
          timeout: false,
          icon: LoadingSvg,
        }
      );

      this.installColors(id, license_id, term_slug);
    },
    installColors(id, license_id, term_slug) {
      const localToast = this.toast.info(
        sprintf(
          __("Installing Colors", "asura-connector"),
          this.term_slug
        ),
        {
          timeout: false,
          icon: LoadingSvg,
        }
      );

      axios
        .post(
          thelostasura.ajax_url,
          new URLSearchParams({
            _wpnonce: thelostasura.nonce,
            action: "asura_connector_wizard_colors",
            overwrite: this.form.overwrite,
            provider_id: id ? id : this.provider_id,
            license_id: license_id,
            term_slug: term_slug,
          })
        )
        .then((response) => {
          this.toast.update(localToast, {
            content: __("Colors installed successfully.", "asura-connector"),
            options: {
              type: "success",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .catch((error) => {
          this.toast.update(localToast, {
            content: __("Failed to install Colors.", "asura-connector"),
            options: {
              type: "error",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .then(() => {
          this.installStylesheets(id, license_id, term_slug);
        });
    },
    installStylesheets(id, license_id, term_slug) {
      const localToast = this.toast.info(
        sprintf(
          __("Installing Stylesheets", "asura-connector"),
          this.term_slug
        ),
        {
          timeout: false,
          icon: LoadingSvg,
        }
      );

      axios
        .post(
          thelostasura.ajax_url,
          new URLSearchParams({
            _wpnonce: thelostasura.nonce,
            action: "asura_connector_wizard_stylesheets",
            overwrite: this.form.overwrite,
            provider_id: id ? id : this.provider_id,
            license_id: license_id,
            term_slug: term_slug,
          })
        )
        .then((response) => {
          this.toast.update(localToast, {
            content: __("Stylesheets installed successfully.", "asura-connector"),
            options: {
              type: "success",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .catch((error) => {
          this.toast.update(localToast, {
            content: __("Failed to install Stylesheets.", "asura-connector"),
            options: {
              type: "error",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .then(() => {
          this.installSettings(id, license_id, term_slug);
        });
    },
    installSettings(id, license_id, term_slug) {
      const localToast = this.toast.info(
        sprintf(
          __("Installing Settings", "asura-connector"),
          this.term_slug
        ),
        {
          timeout: false,
          icon: LoadingSvg,
        }
      );

      axios
        .post(
          thelostasura.ajax_url,
          new URLSearchParams({
            _wpnonce: thelostasura.nonce,
            action: "asura_connector_wizard_settings",
            overwrite: this.form.overwrite,
            provider_id: id ? id : this.provider_id,
            license_id: license_id,
            term_slug: term_slug,
          })
        )
        .then((response) => {
          this.toast.update(localToast, {
            content: __("Settings installed successfully.", "asura-connector"),
            options: {
              type: "success",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .catch((error) => {
          this.toast.update(localToast, {
            content: __("Failed to install Settings.", "asura-connector"),
            options: {
              type: "error",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .then(() => {
          this.installStylesets(id, license_id, term_slug);
        });
    },
    installStylesets(id, license_id, term_slug) {
      const localToast = this.toast.info(
        sprintf(
          __("Installing Stylesets", "asura-connector"),
          this.term_slug
        ),
        {
          timeout: false,
          icon: LoadingSvg,
        }
      );

      axios
        .post(
          thelostasura.ajax_url,
          new URLSearchParams({
            _wpnonce: thelostasura.nonce,
            action: "asura_connector_wizard_stylesets",
            overwrite: this.form.overwrite,
            provider_id: id ? id : this.provider_id,
            license_id: license_id,
            term_slug: term_slug,
          })
        )
        .then((response) => {
          this.toast.update(localToast, {
            content: __("Stylesets installed successfully.", "asura-connector"),
            options: {
              type: "success",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .catch((error) => {
          this.toast.update(localToast, {
            content: __("Failed to install Stylesets.", "asura-connector"),
            options: {
              type: "error",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .then(() => {
          this.installSelectors(id, license_id, term_slug);
        });
    },
    installSelectors(id, license_id, term_slug) {
      const localToast = this.toast.info(
        sprintf(
          __("Installing Selectors", "asura-connector"),
          this.term_slug
        ),
        {
          timeout: false,
          icon: LoadingSvg,
        }
      );

      axios
        .post(
          thelostasura.ajax_url,
          new URLSearchParams({
            _wpnonce: thelostasura.nonce,
            action: "asura_connector_wizard_selectors",
            overwrite: this.form.overwrite,
            provider_id: id ? id : this.provider_id,
            license_id: license_id,
            term_slug: term_slug,
          })
        )
        .then((response) => {
          this.toast.update(localToast, {
            content: __("Selectors installed successfully.", "asura-connector"),
            options: {
              type: "success",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .catch((error) => {
          this.toast.update(localToast, {
            content: __("Failed to install Selectors.", "asura-connector"),
            options: {
              type: "error",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .then(() => {
          this.installTemplates(id, license_id, term_slug);
        });
    },
    installTemplates(id, license_id, term_slug) {
      const localToast = this.toast.info(
        sprintf(
          __("Installing Templates", "asura-connector"),
          this.term_slug
        ),
        {
          timeout: false,
          icon: LoadingSvg,
        }
      );

      axios
        .post(
          thelostasura.ajax_url,
          new URLSearchParams({
            _wpnonce: thelostasura.nonce,
            action: "asura_connector_wizard_templates",
            overwrite: this.form.overwrite,
            provider_id: id ? id : this.provider_id,
            license_id: license_id,
            term_slug: term_slug,
          })
        )
        .then((response) => {
          this.toast.update(localToast, {
            content: __("Templates installed successfully.", "asura-connector"),
            options: {
              type: "success",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .catch((error) => {
          this.toast.update(localToast, {
            content: __("Failed to install Templates.", "asura-connector"),
            options: {
              type: "error",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .then(() => {
          this.installPages(id, license_id, term_slug);
        });
    },
    installPages(id, license_id, term_slug) {
      const localToast = this.toast.info(
        sprintf(
          __("Installing Pages", "asura-connector"),
          this.term_slug
        ),
        {
          timeout: false,
          icon: LoadingSvg,
        }
      );

      axios
        .post(
          thelostasura.ajax_url,
          new URLSearchParams({
            _wpnonce: thelostasura.nonce,
            action: "asura_connector_wizard_pages",
            overwrite: this.form.overwrite,
            provider_id: id ? id : this.provider_id,
            license_id: license_id,
            term_slug: term_slug,
          })
        )
        .then((response) => {
          this.toast.update(localToast, {
            content: __("Pages installed successfully.", "asura-connector"),
            options: {
              type: "success",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .catch((error) => {
          this.toast.update(localToast, {
            content: __("Failed to install Pages.", "asura-connector"),
            options: {
              type: "error",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .then(() => {
          this.installClasses(id, license_id, term_slug);
        });
    },
    installClasses(id, license_id, term_slug) {
      const localToast = this.toast.info(
        sprintf(
          __("Installing Classes", "asura-connector"),
          this.term_slug
        ),
        {
          timeout: false,
          icon: LoadingSvg,
        }
      );

      axios
        .post(
          thelostasura.ajax_url,
          new URLSearchParams({
            _wpnonce: thelostasura.nonce,
            action: "asura_connector_wizard_classes",
            overwrite: this.form.overwrite,
            provider_id: id ? id : this.provider_id,
            license_id: license_id,
            term_slug: term_slug,
          })
        )
        .then((response) => {
          this.toast.update(localToast, {
            content: __("Classes installed successfully.", "asura-connector"),
            options: {
              type: "success",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .catch((error) => {
          this.toast.update(localToast, {
            content: __("Failed to install Classes.", "asura-connector"),
            options: {
              type: "error",
              icon: true,
              timeout: 5000,
            },
          });
        })
        .then(() => {
          this.toast.update(this.toastId, {
            content: __("Installation done.", "asura-connector"),
            options: {
              type: "success",
              icon: true,
              timeout: 5000,
            },
          });
        });
    },

  },
};
</script>
