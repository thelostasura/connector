<template>
  <li
    @click="$emit('goto')"
    :class="isSelected ? 'bg-gray-600' : 'bg-gray-700'"
    class="relative m-1 px-6 py-2 flex items-center space-x-3 hover:bg-gray-600 rounded-sm focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 no-underline cursor-pointer"
  >
    <div class="flex-1 min-w-0">
      <a class="focus:outline-none no-underline">
        <!-- Extend touch target to entire panel -->
        <span class="absolute inset-0" aria-hidden="true"></span>
        <p class="text-sm font-medium text-white">{{ provider.site_title }}</p>
        <p class="text-sm text-gray-200 truncate">{{ provider.provider }}</p>
      </a>
    </div>
  </li>
</template>

<script>
export default {
  props: {
    provider: Object,
  },
  emits: ["goto"],
  computed: {
    isSelected() {
      return this.$route.params?.providerId == this.provider.id ? true : false;
    },
  },
};
</script>