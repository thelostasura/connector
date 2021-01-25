<template>
  <li>
    <router-link 
      :to="designSetLink"
      class="relative m-1 px-6 py-5 flex items-center space-x-3 bg-gray-700 hover:bg-gray-600 rounded-sm focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 no-underline"
    >
      <div class="flex-1 min-w-0">
        <a class="focus:outline-none no-underline">
          <!-- Extend touch target to entire panel -->
          <span class="absolute inset-0" aria-hidden="true"></span>
          <p class="text-sm font-medium text-white">{{ name }}</p>
          <p class="text-sm text-gray-200 truncate">{{ slug }}</p>
        </a>
      </div>
    </router-link>
  </li>
</template>

<script>
export default {
  props: {
    id: Number,
    name: String,
    slug: String,
    providerId: Number,
    licenseId: Number,
  },
  computed: {
    designSetLink() {
      return {
        // name: 'designset-slug',
        // params: {
        //   licenseId: this.licenseId,
        //   designSetSlug: this.slug,
        // }
      };
    }
  },
};
</script>

<style scoped>
.router-link-active {
  --tw-bg-opacity: 1;
  background-color: rgba(75, 85, 99, var(--tw-bg-opacity));
}
</style>