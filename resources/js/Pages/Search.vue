<script setup>
 import UserListItem from "@/Components/app/UserListItem.vue";
 import GroupItem from "@/Components/app/GroupItem.vue";
 import PostList from "@/Components/app/PostList.vue";
 import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

 const  props = defineProps({
     search: String,
     user: Array,
     groups: Array,
     posts: Object
 })
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-4 ">
            <div  class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <div class="shadow bg-white rounded p-3 mb-3">
                    <h2 class="text-lg font-medium text-gray-900">Users</h2>
                    <div class="">
                        <UserListItem v-if="props.user && props.user.length" v-for="user in props.user" :key="user.id" :user="user" />
                        <div v-else class="py-8 text-center">
                            No users were found
                        </div>
                    </div>
                </div>
                <div class="shadow bg-white rounded p-3 mb-3">
                    <h2 class="text-lg font-medium text-gray-900">
                        Groups
                    </h2>
                    <div class="">
                        <GroupItem v-if="groups && groups.length" v-for="group of groups"  :group="group" />
                        <div v-else class="py-8 text-center">
                            No groups were found
                        </div>
                    </div>
                </div>

                <div class="shadow bg-white rounded p-3 mb-3">
                    <h2 class="text-lg font-medium text-gray-900">
                        Posts
                    </h2>
                    <div>
                        <PostList v-if="props.posts" :posts="props.posts" class="flex-1"/>
                        <div v-else class="py-8 text-center">
                            No posts
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
