<template>
    <div class="w-1/5 h-full">
      <!-- This example requires Tailwind CSS v2.0+ -->
      <nav class="relative h-full overflow-y-auto" aria-label="Directory">
        <div
          class="z-10 sticky top-0 border-t border-b border-gray-200 bg-gray-50 px-3 text-sm font-medium text-gray-900"
        >
          <h3>Providers</h3>
        </div>
        <ul class="relative m-0 p-0 z-0 divide-y divide-gray-200 list-none">
          <DesignSetsItem
            v-for="provider in providers"
            :key="provider.id"
            :id="provider.id"
            :site-title="provider.site_title"
            :provider="provider.provider"
          />
        </ul>
      </nav>
    </div>
    <router-view></router-view>
</template>

<script>
import DesignSetsItem from "./DesignSetsItem.vue";

export default {
  components: {
    DesignSetsItem,
  },
  inject: ["busy"],
  data() {
    return {
      providers: []
    };
  },
  beforeRouteUpdate(to, from) {
    if (to.name === "providers-list") {
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
  }
};
</script>