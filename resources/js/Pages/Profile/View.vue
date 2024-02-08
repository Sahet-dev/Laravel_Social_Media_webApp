<template>
    <AuthenticatedLayout>
        <div class="w-[768px] mx-auto h-full overflow-auto">
            <div class="relative bg-white">
                <img src="https://cdn.mos.cms.futurecdn.net/hJHjdbVKNhorF6aNu5p8kC-1200-80.jpg"
                     class="w-full h-[200px] object-cover">
                <div class="flex">
                    <img src="https://icon-library.com/images/avatar-icon-images/avatar-icon-images-4.jpg"
                         class="ml-[48px] w-[128px] h-[128px] -mt-[64px]">
                    <div class="flex justify-between items-center flex-1 p-4">
                        <h2 class="font-bold text-lg">{{ user.name }}</h2>
                        <PrimaryButton v-if="isMyProfile">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                            </svg>
                            Edit Profile</PrimaryButton>
                    </div>
                </div>
            </div>
            <div class="border-t-1">
                <TabGroup>
                    <TabList class=" flex bg-white">
                        <Tab v-if="isMyProfile" v-slot="{ selected }" as="template">
                            <TabItem text="About" :selected="selected"/>
                        </Tab>
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem text="Posts" :selected="selected"/>
                        </Tab>
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem text="Followers" :selected="selected"/>
                        </Tab>
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem text="Following" :selected="selected"/>
                        </Tab>
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem text="Photos" :selected="selected"/>
                        </Tab>
                    </TabList>

                    <TabPanels class="mt-2">
                        <TabPanel v-if="isMyProfile" key="posts" class="">
                            <Edit :must-verify-email="mustVerifyEmail" :status="status"/>
                        </TabPanel>
                        <TabPanel key="posts" class="bg-white p-3 shadow">
                            POSTS
                        </TabPanel>
                        <TabPanel key="posts" class="bg-white p-3 shadow">
                            Followers
                        </TabPanel>
                        <TabPanel key="posts" class="bg-white p-3 shadow">
                            Following
                        </TabPanel>
                        <TabPanel key="followers" class="bg-white p-3 shadow">
                            Photos
                        </TabPanel>
                    </TabPanels>
                </TabGroup>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import {computed, ref} from 'vue'
    import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'

    import {usePage} from "@inertiajs/vue3";
    import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
    import TabItem from "@/Pages/Profile/Partials/TabItem.vue";
    import Edit from "@/Pages/Profile/Edit.vue";
    import PrimaryButton from "@/Components/PrimaryButton.vue";

    const authUser = usePage().props.auth.user;

    const isMyProfile = computed(()=> authUser && authUser.id === props.user.id)

    const props = defineProps({
        mustVerifyEmail: {
            type: Boolean,
        },
        status: {
            type: String,
        },
        user: {
            type: Object,
        }
    });



</script>
