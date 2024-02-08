<script setup>
    defineProps(
        {
            image: String,
            name: String
        }
    )
</script>

<template>
    <div class="mb-3 cursor-pointer hover:bg-gray-50">
        <div class="flex items-center gap-2 py-2 px-3">
            <img :src="image" class="w-[32px] rounded-full"/>
            <div>
                <h3 class="font-black">{{name}}</h3>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
