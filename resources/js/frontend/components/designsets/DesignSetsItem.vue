<template>
  <li
      @mouseover="$emit('show-preview')" 
      @mouseleave="$emit('hide-preview')" 
      @click="$emit('commit')"
      class="relative m-1 px-6 py-2 flex items-center space-x-3 bg-gray-700 hover:bg-gray-600 rounded-sm focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 no-underline cursor-pointer"
    >
      <div class="flex-1 min-w-0">
        <a class="focus:outline-none no-underline">
          <!-- Extend touch target to entire panel -->
          <span class="absolute inset-0" aria-hidden="true"></span>
          <p class="text-sm font-medium text-white">{{ name }}</p>
        </a>
      </div>
  </li>
</template>

<script>
export default {
  props: {
    name: String,
  },
  emits: [
    'commit',
    'show-preview',
    'hide-preview'
  ],
};
</script>
