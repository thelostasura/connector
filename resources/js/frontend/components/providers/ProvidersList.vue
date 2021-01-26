<template>
  <div class="w-1/6 h-full">
    <!-- This example requires Tailwind CSS v2.0+ -->
    <nav class="relative h-full overflow-y-auto" aria-label="Directory">
      <div
        class="z-10 sticky top-0 bg-gray-800 border-t border-b border-gray-200 px-3 text-sm font-medium text-white"
      >
        <h3>{{ __("Providers", "asura-connector") }}</h3>
      </div>
      <ul class="relative m-0 p-0 z-0 divide-y divide-gray-200 list-none">
        <ProvidersItem
          v-for="provider in providers"
          :key="provider.id"
          :provider="provider"
          @goto="goto(provider)"
        />
      </ul>
    </nav>
  </div>
  <router-view></router-view>
</template>

<script>
import ProvidersItem from "./ProvidersItem.vue";
import { useToast } from "vue-toastification";
import LoadingSvg from "../../UI/LoadingSvg.vue";

export default {
  components: {
    ProvidersItem,
  },
  inject: ["busy"],
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      providers: [],
    };
  },
  mounted() {
    this.loadProvidersList();
  },
  methods: {
    goto(provider) {
      this.$router.push({
        name: "terms",
        params: {
          providerId: provider.id,
        },
      });
    },
    loadProvidersList() {
      this.busy();
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
          console.log(error); // give hint to my lovely Connector user
          const toastId = this.toast.error(
            __("Failed to load Providers list", "asura-connector")
          );
        })
        .then(() => {
          this.busy(false);
        });
    },
  },
};
</script>