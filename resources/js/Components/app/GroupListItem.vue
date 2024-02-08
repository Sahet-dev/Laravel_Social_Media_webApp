<script setup>

import GroupItem from "@/Components/app/GroupItem.vue";
import TextInput from "@/Components/TextInput.vue";
</script>

<template>
    <TextInput :model-value="searchKeyword" placeholder="Search for Groups" class="w-full"/>
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
</template>

<style scoped>

</style>
