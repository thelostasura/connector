<template>
    <div class="w-1/6 h-full">
      <!-- This example requires Tailwind CSS v2.0+ -->
      <nav class="relative h-full overflow-y-auto" aria-label="Directory">
        <div
          class="z-10 sticky top-0 bg-gray-800 border-t border-b border-gray-200 px-3 text-sm font-medium text-white"
        >
          <h3>{{ __('Design Sets', 'asura-connector') }}</h3>
        </div>
        <ul class="relative m-0 p-0 z-0 divide-y divide-gray-200 list-none">
          <TermsItem
            v-for="term in terms"
            :key="term.slug"
            :slug="term.slug"
            :name="term.name"
            :license-id="term.license_id"
            :provider-id="term.provider_id"
            @goto="goto(term)"
          />
        </ul>
      </nav>
    </div>
    <router-view></router-view>
</template>

<script>
import TermsItem from "./TermsItem.vue";
import { useToast } from "vue-toastification";
import LoadingSvg from "../../UI/LoadingSvg.vue";

export default {
  components: {
    TermsItem,
  },
  inject: ["busy"],
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      terms: []
    };
  },
  beforeRouteUpdate(to, from) {
    if (to.name === "terms") {
      this.loadTermsList(to.params);
    }
  },
  mounted() {
    this.loadTermsList(this.$route.params);
  },
  methods: {
    goto(term) {
      this.$router.push({
        name: 'designsets',
        params: {
          // providerId: term.provider_id,
          licenseId: term.license_id,
          termSlug: term.slug,
        }
      });
    },
    loadTermsList(params) {
      this.busy();
      axios
        .get(thelostasura.ajax_url, {
          params: {
            action: "asura_connector_list_terms",
            provider_id: params.providerId,
            _wpnonce: thelostasura.nonce,
          },
        })
        .then((response) => {
          if (response.status === 200) {
            this.terms = response.data.data;
          }
        })
        .catch((error) => {
          const toastId = this.toast.error(
            __("Failed to load Term list", "asura-connector")
          );
        })
        .then(() => {
          this.busy(false);
        });
    },
  }
};
</script>