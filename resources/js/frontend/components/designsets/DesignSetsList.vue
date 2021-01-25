<template>
  <div class="w-1/5 h-full">
    <!-- This example requires Tailwind CSS v2.0+ -->
    <nav class="relative h-full overflow-y-auto" aria-label="Directory">
      <div class="mb-2">
        <span
          class="inline-flex rounded-md shadow-sm w-full justify-center"
          ><a
            v-if="showPagesButton"
            @click="selectedType = 'pages'"
            :title="__('Pages', 'asura-connector')"
            :class="selectedType == 'pages' ? 'bg-gray-600' : 'bg-gray-700'"
            class="cursor-pointer whitespace-no-wrap inline-flex items-center justify-center w-10 h-10 px-2 py-2 m-1 border border-transparent text-base leading-6 font-medium rounded-md text-white hover:bg-gray-600 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150"
            ><svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"
              ></path></svg
          ></a>
          <a
            v-if="showComponentsButton"
            @click="selectedType = 'components'"
            :title="__('Section & Elements', 'asura-connector')"
            :class="selectedType == 'components' ? 'bg-gray-600' : 'bg-gray-700'"
            class="cursor-pointer whitespace-no-wrap inline-flex items-center justify-center w-10 h-10 px-2 py-2 m-1 border border-transparent text-base leading-6 font-medium rounded-md text-white hover:bg-gray-600 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150"
            ><svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"
              ></path></svg></a
        ></span>
      </div>

      <template v-if="selectedType !== null">
        <div class="z-10 sticky top-0 bg-gray-800 border-t border-b border-gray-200 px-3 text-sm font-medium text-white">
          <h3>{{ __("Categories", "asura-connector") }}</h3>
        </div>
        <ul class="relative m-0 p-0 z-0 divide-y divide-gray-200 list-none">
          <CategoriesList
            name="All"
            :slug="null"
            @update-category="updateCategory(null)"
          />
          <CategoriesList
            v-for="category in categories"
            :key="category.key"
            :name="category.label"
            :slug="category.key"
            @update-category="updateCategory(category.key)"
          />
        </ul>
      </template>

    </nav>
  </div>
  <router-view></router-view>
</template>

<script>
import DesignSetsItem from "./DesignSetsItem.vue";
import CategoriesList from "./CategoriesList.vue";
import { useToast } from "vue-toastification";
import LoadingSvg from "../../UI/LoadingSvg.vue";

export default {
  components: {
    DesignSetsItem,
    CategoriesList,
  },
  inject: ["busy"],
  provide() {
    return {
      selectedType: this.selectedType,
    }
  },
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      designsets: [],
      selectedType: null,
      selectedCategory: null
    };
  },
  computed: {
    showPagesButton() {
      return this.designsets?.pages?.length > 0;
    },
    showComponentsButton() {
      return this.designsets?.components?.length > 0;
    },
    categories() {
      if (this.selectedType === 'pages' && this.designsets?.categories?.pages?.length > 0) {
        return this.designsets.categories.pages;
      }
      if (this.selectedType === 'components' && this.designsets?.categories?.components?.length > 0) {
        return this.designsets.categories.components;
      }
      return [];
    }
  },
  beforeRouteUpdate(to, from) {
    if (to.name === "terms" || to.name === "designsets") {
      this.loadDesignsList(to.params);
    }
  },
  mounted() {
    this.loadDesignsList(this.$route.params);
  },
  methods: {
    loadDesignsList(params) {
      this.busy();
      axios
        .get(thelostasura.ajax_url, {
          params: {
            action: "asura_connector_list_designsets",
            provider_id: params.providerId,
            license_id: params.licenseId,
            term_slug: params.termSlug,
            _wpnonce: thelostasura.nonce,
          },
        })
        .then((response) => {
          if (response.status === 200) {
            this.designsets = response.data.data;
          }
        })
        .catch((error) => {
          const toastId = this.toast.error(
            __("Failed to load Design  list", "asura-connector")
          );
        })
        .then(() => {
          this.busy(false);
        });
    },
    updateCategory(key) {
      this.selectedCategory = key;
    }
  },
};
</script>