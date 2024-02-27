<script setup>

import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { Menu, MenuButton, MenuItems, MenuItem  } from '@headlessui/vue'
import { PencilIcon, TrashIcon, EllipsisVerticalIcon, HandThumbUpIcon } from '@heroicons/vue/20/solid'
import { ChatBubbleLeftRightIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline'
import {router} from "@inertiajs/vue3";
import {isImage} from "@/helpers.js";
import {PaperClipIcon} from "@heroicons/vue/24/solid/index.js";
import axiosClient from "@/axiosClient.js";


const props = defineProps({
        post: Object,
    });


const emit = defineEmits(['editClick', 'attachmentClick'])
// function isImage(attachment){
//     const mime = attachment.mime.split('/')
//     return mime[0].toLowerCase() === 'image'
//
// }

function openEditModal (){
    emit('editClick', props.post)
}

function deletePost(){
    if (window.confirm('Do you want to Delete this Post?')){
        router.delete(route('post.destroy', props.post), {
            preserveScroll: true
        })
    }
}

function openAttachment(ind){
    emit('attachmentClick', props.post, ind)
    console.log(attachment)
}

function sendReaction(){
    axiosClient.post(route('post.reaction', props.post), {
        reaction: 'like'
    })
        .then(({data}) => {
            props.post.current_user_has_reaction = data.current_user_has_reaction
            props.post.num_of_reactions = data.num_of_reactions;

        })
}

</script>

<template>
    <div class="bg-white border rounded px-4 shadow mb-3 pt-3">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center  gap-2">
                <a href="javascript:void(0)">
                    <img :src="post.user.avatar" class="w-[40px] rounded-full border border-2 transition-all hover:border-blue-500" alt=""/>
                </a>
                <div>
                    <h4 class="font-bold">
                        <a href="javascript:void(0)" class="hover:underline">{{post.user.name}}</a> >
                        <template v-if="post.group">
                            <a href="javascript:void(0)" class="hover:underline">
                                {{post.group.name}}
                            </a>
                        </template>
                    </h4>
                    <small class="text-gray-400">{{post.created_at}}</small>
                </div>
            </div>
            <Menu as="div" class="relative z-10 inline-block text-left">
                            <div>
                                <MenuButton
                                    class="w-8 h-8 rounded-full hover:bg-black/5 transition flex items-center
                                        justify-center"
                                >
                                    <EllipsisVerticalIcon
                                        class="w-5 h-5"
                                        aria-hidden="true"
                                    />
                                </MenuButton>
                            </div>

                            <transition
                                enter-active-class="transition duration-100 ease-out"
                                enter-from-class="transform scale-95 opacity-0"
                                enter-to-class="transform scale-100 opacity-100"
                                leave-active-class="transition duration-75 ease-in"
                                leave-from-class="transform scale-100 opacity-100"
                                leave-to-class="transform scale-95 opacity-0"
                            >
                                <MenuItems
                                    class="absolute right-0 mt-2 w-32 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none"
                                >
                                    <div class="px-1 py-1">
                                        <MenuItem v-slot="{ active }">
                                            <button
                                                @click="openEditModal"
                                                :class="[
                                                      active ? 'bg-indigo-600 text-white' : 'text-gray-900',
                                                      'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                                                    ]"
                                            >
                                                <PencilIcon
                                                    class="mr-2 h-5 w-5"
                                                    aria-hidden="true"
                                                /> Edit
                                            </button>


                                        </MenuItem>
                                        <MenuItem v-slot="{ active }">
                                            <button
                                                @click="deletePost"
                                                :class="[
                                                      active ? 'bg-indigo-600 text-white' : 'text-gray-900',
                                                      'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                                                    ]"
                                            >
                                                <TrashIcon
                                                    class="mr-2 h-5 w-5"
                                                    aria-hidden="true"
                                                /> Delete
                                            </button>


                                        </MenuItem>
                                    </div>
                                </MenuItems>
                            </transition>
                        </Menu>
        </div>
        <div class="mb-3">
            <Disclosure v-slot="{ open }">
                <div class="ck-content-output" v-if="!open" v-html="post.body.substring(0, 200)" />
                <template v-if="post.body.length > 200">
                    <DisclosurePanel class="">
                        <div class="ck-content-output" v-html="post.body" />
                    </DisclosurePanel>
                    <div class="flex justify-end">
                        <DisclosureButton
                            class="text-blue-500 hover:underline">
                            {{open ? 'Read less' : 'Read more'}}
                        </DisclosureButton>
                    </div>
                </template>
            </Disclosure>
        </div>
        <div class="grid gap-3 mb-3 z-50" :class="[
            post.attachments.length === 1 ? 'grid-cols-1' : 'grid-cols-2'
        ]">
            <template v-for="(attachment, ind) of post.attachments.slice(0, 4)">
                <div @click="openAttachment(ind)" class="group aspect-square items-center justify-center bg-blue-100
                flex flex-col items-center justify-center text-gray-500 relative cursor-pointer">
                    <div v-if="ind === 3 && post.attachments.length > 4" class="absolute left-0 top-0 right-0 bottom-0 z-50 bg-black/40 text-white flex items-center justify-center">
                        + {{ post.attachments.length - 4 }} more
                    </div>
                    <a @click.stop :href="route('post.download', attachment)" class="opacity-0 group-hover:opacity-100 transition-all w-8 h-8 flex items-center justify-center text-gray-100 bg-gray-700
                     rounded absolute right-2 top-2 cursor-pointer hover:bg-gray-800">
                        <ArrowDownTrayIcon class="w-4 h-4"/>
                    </a>
                    <img v-if="isImage(attachment)" :src="attachment.url"
                        class="object-contain aspect-square">
                    <div v-else class="flex flex-col justify-center items-center">
                        <PaperClipIcon class="w-10 h-10 mb-3"/>
                        <small>{{attachment.name}}</small>
                    </div>
                </div>
            </template>
        </div>
        <div class="flex gap-2 pb-3">
            <button @click="sendReaction"
                class="flex gap-1 items-center justify-center bg-gray-100 rounded-lg hover:bg-gray-200
                py-2 px-4 flex-1" :class="
                [post.current_user_has_reaction ? 'bg-sky-100 hover:bg-sky-200' : 'bg-gary-100 hover:bg-gray-200']">
                <HandThumbUpIcon class="w-5 h-5"/>
                <span class="mr-2">
                    {{post.num_of_reactions}}
                </span>

                    {{post.current_user_has_reaction ? 'Unlike' : 'Like'}}

            </button>
            <button class="flex gap-1 items-center justify-center bg-gray-100 rounded-lg hover:bg-gray-200 py-2 px-4 flex-1">
                <ChatBubbleLeftRightIcon class="w-5 h-5 mr-2"/>
                Comment
            </button>
        </div>
    </div>
</template>

<style>

</style>
