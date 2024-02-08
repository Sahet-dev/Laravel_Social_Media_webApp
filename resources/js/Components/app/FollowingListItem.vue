<script setup>

import FriendComp from "@/Components/app/FriendComp.vue";
import TextInput from "@/Components/TextInput.vue";
import {ref} from "vue";

const searchKeyword = ref('');
</script>

<template>
    <TextInput :model-value="searchKeyword" placeholder="Search for Groups" class="w-full"/>
    <div class="mt-3 h-[200px] lg:flex-1 overflow-auto">
        <div v-if="false" class="text-gray-400 text-center p-3">
            You are not following to anyone yet
        </div>
        <div v-else>
            <FriendComp image="https://picsum.photos/100" name="Jonh Lark" />
            <FriendComp image="https://picsum.photos/110" name="Nill Cafery" />
            <FriendComp image="https://picsum.photos/109" name="Jonh Lark" />
            <FriendComp image="https://picsum.photos/108" name="Nill Cafery" />
            <FriendComp image="https://picsum.photos/107" name="Jonh Lark" />
            <FriendComp image="https://picsum.photos/106" name="Nill Cafery" />
            <FriendComp image="https://picsum.photos/106" name="Jonh Lark" />
            <FriendComp image="https://picsum.photos/105" name="Nill Cafery" />
            <FriendComp image="https://picsum.photos/104" name="Jonh Lark" />
            <FriendComp image="https://picsum.photos/103" name="Nill Cafery" />
            <FriendComp image="https://picsum.photos/102" name="Jonh Lark" />
            <FriendComp image="https://picsum.photos/101" name="Nill Cafery" />
        </div>
    </div>
</template>

<style scoped>

</style>
