<script setup>
import {Disclosure, DisclosureButton, DisclosurePanel} from '@headlessui/vue'
import {HandThumbUpIcon} from '@heroicons/vue/20/solid'
import {ChatBubbleLeftRightIcon} from '@heroicons/vue/24/outline'
import {Link, router, usePage} from "@inertiajs/vue3";
import axiosClient from "@/axiosClient.js";
import ReadMoreReadLess from "@/Components/app/ReadMoreReadLess.vue";
import EditDeleteDropdown from "@/Components/app/EditDeleteDropdown.vue";
import {ArrowDownTrayIcon} from '@heroicons/vue/24/outline'
import {isImage} from '@/helpers.js'
import {PaperClipIcon } from '@heroicons/vue/24/solid/index.js'
import CommentList from "@/Components/app/CommentList.vue";
import {ChevronRightIcon} from "@heroicons/vue/24/solid/index.js";

const authUser = usePage().props.auth.user

const props = defineProps({
        post: Object,
    });

const emit = defineEmits(['editClick', 'attachmentClick'])

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
                <Link :href="route('profile', post.user.username)">
                    <img :src="post.user.avatar_url" class="w-[40px] rounded-full border border-2 transition-all hover:border-blue-500" alt=""/>
                </Link>
                <div>
                    <h4 class="flex align-items-center font-bold">
                        <a :href="route('profile', post.user.username)" class="hover:underline">{{post.user.name}}</a>
                        <template v-if="post.group">
                            <ChevronRightIcon class="w-4"/>
                            <Link :href="route('group.profile', post.group.slug)" class="hover:underline">
                                {{post.group.name}}
                            </Link>
                        </template>
                    </h4>
                    <small class="text-gray-400">{{post.created_at}}</small>
                </div>
            </div>
            <EditDeleteDropdown :user="post.user" :post="post" @edit="openEditModal" @delete="deletePost"/>
        </div>
        <div class="mb-3">
            <ReadMoreReadLess :content="post.body"  content-class="text-sm flex flex-1"/>
        </div>
        <div class="grid gap-3 mb-3 z-50" :class="[
            post.attachments.length === 1 ? 'grid-cols-1' : 'grid-cols-2'
        ]">

                <template v-for="(attachment, ind) of post.attachments.slice(0, 4)">
                    <div @click="openAttachment(ind)" class="group aspect-square items-center justify-center bg-blue-100
                flex flex-col items-center justify-center text-gray-500 relative cursor-pointer">
                        <div v-if="ind === 3 && post.attachments.length > 4"
                             class="absolute left-0 top-0 right-0 bottom-0 z-50 bg-black/40 text-white flex items-center justify-center">
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
        <Disclosure v-slot="{ open }">
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
                <DisclosureButton
                    class="flex gap-1 items-center justify-center bg-gray-100 rounded-lg hover:bg-gray-200 py-2 px-4 flex-1"
                >
                    <ChatBubbleLeftRightIcon class="w-5 h-5 "/>
                    <span class="mr-2">
                        {{post.num_of_comments}}
                    </span>
                    Comment
                </DisclosureButton>
            </div>
            <DisclosurePanel class="comment-list mt-3 max-h-[400] overflow-auto">
                <CommentList :post="post" :data="{comments: post.comments}"/>
            </DisclosurePanel>
        </Disclosure>
    </div>
</template>

