<script setup>

import GroupItem from "@/Components/app/GroupItem.vue";
import TextInput from "@/Components/TextInput.vue";
import {ref} from "vue";
import GroupModal from "@/Components/app/GroupModal.vue";

const showNewGroupModal = ref(false);

const searchKeyword = ref('');
</script>

<template>
    <div class="flex gap-2 mt-2">
        <TextInput :model-value="searchKeyword" placeholder="Search for Groups" class="w-full"/>
        <button @click="showNewGroupModal = true" class="text-sm bg-indigo-500 hover:bg-indigo-600
                text-white rounded py-1 px-2 mb-4 w-min-[120px]">
            create group
        </button>
    </div>

    <div class="mt-3 h-[200px] lg:flex-1 overflow-auto">
        <div v-if="false" class="text-gray-400 text-center p-3">
            You are not joined to any groups
        </div>
        <div v-else class="">
            <GroupItem image="https://picsum.photos/100" title="Laravel Devs"
                       description="Lorem ipsum dolor sit amet,consectetur adipiscing elit, sed d" />
            <GroupItem image="https://picsum.photos/100" title="Laravel Devs Supportter"
                       description="Lorem ipsum dolor sit amet,consectetur adipiscing elit, sed d" /> <GroupItem image="https://picsum.photos/100" title="Laravel Devs"
                                                                                                                 description="Lorem ipsum dolor sit amet,consectetur adipiscing elit, sed d" />
            <GroupItem image="https://picsum.photos/100" title="Laravel Devs Supportter"
                       description="Lorem ipsum dolor sit amet,consectetur adipiscing elit, sed d" /> <GroupItem image="https://picsum.photos/100" title="Laravel Devs"
                                                                                                                 description="Lorem ipsum dolor sit amet,consectetur adipiscing elit, sed d" />
            <GroupItem image="https://picsum.photos/100" title="Laravel Devs Supportter"
                       description="Lorem ipsum dolor sit amet,consectetur adipiscing elit, sed d" /> <GroupItem image="https://picsum.photos/100" title="Laravel Devs"
                                                                                                                 description="Lorem ipsum dolor sit amet,consectetur adipiscing elit, sed d" />
            <GroupItem image="https://picsum.photos/100" title="Laravel Devs Supportter"
                       description="Lorem ipsum dolor sit amet,consectetur adipiscing elit, sed d" /> <GroupItem image="https://picsum.photos/100" title="Laravel Devs"
                                                                                                                 description="Lorem ipsum dolor sit amet,consectetur adipiscing elit, sed d" />
            <GroupItem image="https://picsum.photos/100" title="Laravel Devs Supportter"
                       description="Lorem ipsum dolor sit amet,consectetur adipiscing elit, sed d" /> <GroupItem image="https://picsum.photos/100" title="Laravel Devs"
                                                                                                                 description="Lorem ipsum dolor sit amet,consectetur adipiscing elit, sed d" />
            <GroupItem image="https://picsum.photos/100" title="Laravel Devs Supportter"
                       description="Lorem ipsum dolor sit amet,consectetur adipiscing elit, sed d" /> <GroupItem image="https://picsum.photos/100" title="Laravel Devs"
                                                                                                                 description="Lorem ipsum dolor sit amet,consectetur adipiscing elit, sed d" />
            <GroupItem image="https://picsum.photos/100" title="Laravel Devs Supportter"
                       description="Lorem ipsum dolor sit amet,consectetur adipiscing elit, sed d" />
        </div>
    </div>
    <GroupModal v-model="showNewGroupModal" />

</template>

<style scoped>

</style>
