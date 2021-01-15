<template>
    <tr>
        <td class="pl-6 py-3 max-w-max-content whitespace-nowrap text-base leading-5 font-medium text-gray-900 tracking-wider">
            <div class="lg:pl-2 text-sm font-normal">
                <strong> {{ siteTitle }} </strong> 
                <span class="text-xs leading-5 text-gray-400"> ID: {{id}} </span>
            </div>
        </td>


        <td class="py-3 max-w-max-content whitespace-nowrap text-sm leading-5 font-medium text-gray-900 tracking-wider">
            <div class="flex items-center space-x-3">
                <code class="px-2 py-1 bg-gray-100"> {{ provider }} </code>
            </div>
        </td>

        <td class="py-3 pr-4 max-w-max-content whitespace-nowrap text-right text-sm leading-5 font-medium text-gray-900 tracking-wider">
            {{ updatedAt }}
        </td>
        <td class="py-3 whitespace-nowrap text-sm text-gray-500 text-right">
            <router-link :to="providerLicensesLink" class="group flex w-full justify-center items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">
                <!-- Heroicon name: key -->
                <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"> -->
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
                Manage License
            </router-link>
        </td>
        <td class="py-3 whitespace-nowrap text-sm text-gray-500 text-right">
            <button @click="$emit('edit')" class="group flex w-full justify-center items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">
                <!-- Heroicon name: pencil-alt -->
                <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                    <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                </svg>
                Edit
            </button>
        </td>
        <td class="py-3 whitespace-nowrap text-sm text-gray-500 text-right">
            <button @click="$emit('delete')" class="group flex w-full justify-center items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">
                <!-- Heroicon name: trash -->
                <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Delete
            </button>
        </td>
    </tr>
</template>

<script>
export default {
    emits: ['delete', 'edit'],
    props: {
        id: Number,
        siteTitle: String,
        provider: String,
        status: Boolean,
        createdAt: String,
        updatedAt: String
    },
    computed: {
        providerLicensesLink() {
            return {
                name: 'licenses',
                params: {
                    providerId: this.id
                }
            };
        }
    }
}
</script>

<style>

</style>