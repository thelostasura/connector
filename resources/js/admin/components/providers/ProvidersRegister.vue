<template>
  <div class="p-8 bg-gray-50">
    <div class="sm:mt-0">
      <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
          <div class="px-4 sm:px-0">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
              {{ __("Add Provider", "asura-connector") }}
            </h3>
            <p class="mt-1 text-sm leading-5 text-gray-600">
              {{
                __(
                  "Add Provider to access design set that managed using thelostasura.com plugin",
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

            <form @submit.prevent="doSave">
              <div class="px-4 py-5 bg-white sm:p-6">
                <div class="grid grid-cols-6 gap-6">
                  <div class="col-span-6">
                    <label
                      for="connector"
                      class="block text-sm font-medium leading-5 text-gray-700"
                    >
                      {{ __("Connector String", "asura-connector") }}
                      <span class="text-red-800">*</span></label
                    >
                    <div class="mt-1 relative rounded-md shadow-sm">
                      <div
                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                      >
                        <!-- Heroicon name: link -->
                        <svg
                          class="h-5 w-5 text-gray-400"
                          xmlns="http://www.w3.org/2000/svg"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke="currentColor"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                          />
                        </svg>
                      </div>
                      <input
                        type="text"
                        v-model="form.connector"
                        id="connector"
                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm sm:leading-5 border-gray-300 rounded-md placeholder-gray-500 placeholder-opacity-50"
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
                  {{ __("Cancel", "asura-connector") }}
                </router-link>
                <button
                  type="submit"
                  class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:order-1 sm:ml-3"
                >
                  {{ __("Save", "asura-connector") }}
                </button>
              </div>
            </form>
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
  components: [LoadingSvg],
  data() {
    return {
      form: {
        connector: "",
        errors: [],
      },
    };
  },
  computed: {
    errorMessageHeader() {
      return sprintf(
        /* translators: %s: number of error(s)  */
        __("There were %s error(s) with your submission", "asura-connector"),
        this.form.errors.length
      );
    },
  },
  methods: {
    doSave() {
      this.form.errors = [];

      const toastId = this.toast.info(
        __("Adding new provider ...", "asura-connector"),
        {
          timeout: false,
          icon: LoadingSvg,
        }
      );

      axios
        .post(
          thelostasura.ajax_url,
          new URLSearchParams({
            action: "asura_connector_add_provider",
            connector: this.form.connector,
            _wpnonce: thelostasura.nonce,
          })
        )
        .then((response) => {
          this.toast.update(toastId, {
            content: __("New Provider added successfully.", "asura-connector"),
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
            content: __("Failed to add new Provider.", "asura-connector"),
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
  mounted() {},
  created() {},
};
</script>