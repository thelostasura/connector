<template>
  <li>
    <router-link 
      :to="designSetLink"
      class="relative px-6 py-5 flex items-center space-x-3 hover:bg-gray-50 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 no-underline"
    >
      <div class="flex-1 min-w-0">
        <a class="focus:outline-none no-underline">
          <!-- Extend touch target to entire panel -->
          <span class="absolute inset-0" aria-hidden="true"></span>
          <p class="text-sm font-medium text-gray-900">{{ siteTitle }}</p>
          <p class="text-sm text-gray-500 truncate">{{ provider }}</p>
        </a>
      </div>
    </router-link>
  </li>
</template>

<script>
export default {
  props: {
    id: Number,
    siteTitle: String,
    provider: String,
  },
  computed: {
    designSetLink() {
      return {
        name: 'provider',
        params: {
          providerId: this.id
        }
      };
    }
  },
};
</script>

<style scoped>
.router-link-active {
  --tw-bg-opacity: 1;
  background-color: rgba(243, 244, 246, var(--tw-bg-opacity));
}
</style>