<template>
  <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
    <div class="w-0 flex-1 flex items-center">
      <!-- Heroicon name: paper-clip -->
      <svg
        class="flex-shrink-0 h-5 w-5 text-gray-400"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 20 20"
        fill="currentColor"
        aria-hidden="true"
      >
        <path
          fill-rule="evenodd"
          d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z"
          clip-rule="evenodd"
        />
      </svg>
      <span class="ml-2 flex-1 w-0 truncate"> {{ name }}  [<span class="font-bold">{{ slug }}</span>] </span>
    </div>
    <div class="ml-4 flex-shrink-0">
      <button
        @click="$emit('install')"
        class="font-medium text-indigo-600 hover:text-indigo-500"
      >
        Install
      </button>
    </div>
  </li>
</template>

<script>
export default {
  emits: ["install"],
  props: {
    name: String,
    slug: String,
  },
};
</script>
