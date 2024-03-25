<script setup>

import UserListItem from "@/Components/app/UserListItem.vue";
import TextInput from "@/Components/TextInput.vue";
import {ref} from "vue";

defineProps({
    followings: Array
})



const searchKeyword = ref('');
</script>

<template>
    <TextInput :model-value="searchKeyword" placeholder="Search for Groups" class="w-full"/>
    <div class="mt-3 h-[200px] lg:flex-1 overflow-auto">
        <div v-if="false" class="text-gray-400 text-center p-3">
            You are not following to anyone yet
        </div>
        <div v-else>
            <UserListItem v-for="following of followings"
                          :key="following.id"
                          :user="following"
                            class="rounded-lg"/>
        </div>
    </div>
</template>

<style scoped>

</style>
