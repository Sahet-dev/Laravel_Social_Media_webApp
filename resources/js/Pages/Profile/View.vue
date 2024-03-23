<script setup>
import {computed, ref, watch} from 'vue'
import {XMarkIcon, CheckCircleIcon, CameraIcon} from "@heroicons/vue/24/solid";
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'

import {usePage} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TabItem from "@/Pages/Profile/Partials/TabItem.vue";
import Edit from "@/Pages/Profile/Edit.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { reactive} from "vue";
import {useForm} from "@inertiajs/vue3";
import WhiteButton from "@/Components/app/WhiteButton.vue";

const imagesForm = useForm({
    avatar: null,
    cover: null,
})
const showNotification = ref(true);
const coverImageSrc = ref('')
const avatarImageSrc = ref('')
const authUser = usePage().props.auth.user;

const isMyProfile = computed(()=> authUser && authUser.id === props.user.id)

const props = defineProps({
    errors: Object,
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    success: {
        type: String,
    },
    isCurrentUserFollower: {
        type: Boolean,
    },
    followerCount: Number,
    user: {
        type: Object,
    }
});

function onCoverChange(event){
    imagesForm.cover = event.target.files[0]
    if (imagesForm.cover){
        const reader = new FileReader()
        reader.onload = ()=> {
            coverImageSrc.value = reader.result;
        }
        reader.readAsDataURL(imagesForm.cover)
    }
}
function onAvatarChange(event){
    imagesForm.avatar = event.target.files[0]
    if (imagesForm.avatar){
        const reader = new FileReader()
        reader.onload = ()=> {
            avatarImageSrc.value = reader.result;
        }
        reader.readAsDataURL(imagesForm.avatar)
    }
}

function cancelCoverImg(){
    imagesForm.cover = null;
    coverImageSrc.value = null;
}
function cancelAvatarImg(){
    imagesForm.avatar = null;
    avatarImageSrc.value = null;
}
function submitCoverImg(){
    imagesForm.post(route('profile.updateImages'),{
        preserveScroll: true,

        onSuccess: (user)=>{
            showNotification.value = true;
            cancelCoverImg();
            setTimeout(()=>{showNotification.value=false}, 5000)
        }
    });
}
function submitAvatarImg(){
    imagesForm.post(route('profile.updateImages'),{
        preserveScroll: true,
        onSuccess: (user)=>{
            showNotification.value = true;
            cancelAvatarImg();
            setTimeout(()=>{showNotification.value=false}, 5000)
        }
    });
}



function followUser(){
    const form = useForm({
        follow: !props.isCurrentUserFollower
    })
    form.post(route('user.follow', props.user.id), {
        preserveScroll: true
    })
}
</script>


<template>
    <AuthenticatedLayout>
        <div class="max-w-[768px] mx-auto h-full overflow-auto">
            <div
                v-show="showNotification && success"
                class="my-2 py-2 px-3 font-medium text-sm bg-emerald-500 text-white"
            >
                {{success}}
            </div>
            <div
                v-if="errors.cover"
                class="my-2 py-2 px-3 font-medium text-sm bg-red-400 text-white"
            >
                {{errors.cover}}
            </div>
            <div class="group  relative bg-white">
                <img :src="coverImageSrc || user.cover_url || '/img/cover.jpg'"
                     class="w-full h-[200px] object-cover">
                <div class="absolute top-2 right-2 ">
                    <button v-if="!coverImageSrc" class="bg-gray-50 hover:bg-gray-100 text-gray-800 py-1 px-2 text-xs flex items-center opacity-0 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                        </svg>

                        Update cover image
                        <input type="file" class="absolute left-0 bottom-0 right-0 opacity-0 cursor-pointer"
                               @change="onCoverChange">
                    </button>
                    <div v-else class="flex gap-2 bg-white p-2  opacity-0 group-hover:opacity-100">
                        <button @click="cancelCoverImg" class="bg-gray-50 hover:bg-gray-100 text-gray-800 py-1 px-2 text-xs flex items-center ">
                            <XMarkIcon class="w-3 h-3 mr-1"/>
                            Cancel
                        </button>
                        <button @click="submitCoverImg" class="bg-gray-800 hover:bg-gray-900 text-gray-100 py-1 px-2 text-xs flex items-center">
                            <CheckCircleIcon class="w-3 h-3 mr-2"/>
                            Submit
                        </button>
                    </div>
                </div>
                <div class="flex">
                    <div class="flex items-center justify-center relative group/avatar -mt-[64px] ml-[48px] w-[128px] h-[128px]  rounded-full overflow-hidden ">
                        <img :src="avatarImageSrc || user.avatar_url || '/img/avatar_default.png'"
                             class="w-full h-full object-cover">
                            <button v-if="!avatarImageSrc"
                                    class="absolute bg-white/25 rounded-full flex items-center justify-center opacity-0 group-hover/avatar:opacity-100">
                                <CameraIcon class="w-8 h-8"/>

                                <input type="file" class="absolute left-0 bottom-0 right-0 opacity-0 cursor-pointer"
                                       @change="onAvatarChange">
                            </button>
                            <div v-else class="absolute items-center justify-center flex l gap-2">
                                <button @click="cancelAvatarImg" class="w-7 h-7 flex items-center justify-center bg-red-500/80
                                text-white rounded-full">
                                    <XMarkIcon class="w-5 h-5 "/>

                                </button>
                                <button @click="submitAvatarImg" class="w-7 h-7 flex items-center justify-center bg-emerald-500/80
                                text-white rounded-full">
                                    <CheckCircleIcon class="w-5 h-5"/>

                                </button>
                            </div>

                    </div>
                    <div class="flex justify-between items-center flex-1 p-4">
                        <div>
                            <h2 class="font-bold text-lg">{{ user.name }}</h2>
                            <p class="text-xs text-gray-500">{{followerCount}} Follower(s)</p>
                        </div>
                        <div>
                            <PrimaryButton v-if="!isCurrentUserFollower" @click="followUser">
                                Follow User
                            </PrimaryButton>

                            <WhiteButton v-else @click="followUser">
                                Unfollow
                            </WhiteButton>

                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t-1">
                <TabGroup>
                    <TabList class=" flex bg-white">
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
                        <Tab v-if="isMyProfile" v-slot="{ selected }" as="template">
                            <TabItem text="My Profile" :selected="selected"/>
                        </Tab>
                    </TabList>

                    <TabPanels class="mt-2">

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
                        <TabPanel v-if="isMyProfile" key="posts" class="">
                            <Edit :must-verify-email="mustVerifyEmail" :status="status"/>
                        </TabPanel>
                    </TabPanels>
                </TabGroup>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
