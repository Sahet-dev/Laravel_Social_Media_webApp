<script setup>
defineProps(
    {
        selected: Boolean,
        text: String
    }
)
</script>

<template>
    <button
        :class="['px-3 py-2.5 outline-none focus:outline text-sm',
                 selected
                 ? 'bg-white text-blue-700 border-b-2 border-blue-500'
                 : 'text-gray-700 hover:text-gray-900',]">
        {{ text }}
    </button>
</template>

<style scoped>

</style>
