<script setup>

import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { Menu, MenuButton, MenuItems, MenuItem  } from '@headlessui/vue'
import { PencilIcon, TrashIcon, EllipsisVerticalIcon, HandThumbUpIcon } from '@heroicons/vue/20/solid'
import { ChatBubbleLeftRightIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline'
import {router, usePage} from "@inertiajs/vue3";
import {isImage} from "@/helpers.js";
import {PaperClipIcon} from "@heroicons/vue/24/solid/index.js";
import axiosClient from "@/axiosClient.js";
import InputTextArea from "@/Components/app/InputTextArea.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import IndigoButton from "@/Components/app/IndigoButton.vue";
import {ref} from "vue";
import ReadMoreReadLess from "@/Components/app/ReadMoreReadLess.vue";
import EditDeleteDropdown from "@/Components/app/EditDeleteDropdown.vue";


const authUser = usePage().props.auth.user

const editingComment = ref(null);


const props = defineProps({
        post: Object,
    });

const newCommentText = ref('');


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

function createComment(){
    axiosClient.post(route('post.comment.create', props.post), {
        comment: newCommentText.value
    })
        .then(({data}) => {
            newCommentText.value = ''
            props.post.comments.unshift(data)
            props.post.num_of_comments++;
            console.log(data)
        })
        .catch((error) => {
            console.error('Error creating comment:', error);
        });
}
function startEditComment(comment){
    editingComment.value = {
        id: comment.id,
        comment: comment.comment.replace(/<br\s*\/?>/gi, '\n')
    }
}

function updateComment(){
    axiosClient.put(route('post.comment.update', editingComment.value.id), editingComment.value)
        .then(({data}) => {
            editingComment.value = null
            props.post.comments = props.post.comments.map((c)=> {
                if (c.id ===data.id){
                    return data
                }
                return c;
            })

            console.log(data)
        })
        .catch((error) => {
            console.error('Error creating comment:', error);
        });
}

function deleteComment(comment){
    if (!window.confirm('Delete Comment?')){
        return false;
    }
    axiosClient.delete(route('post.comment.delete', comment.id))
        .then(({data}) => {
            props.post.comments = props.post.comments.filter(c=> c.id !== comment.id)
            props.post.num_of_comments--;
            console.log(data)
        })
        .catch((error) => {
            console.error('Error creating comment:', error);
        });
}

</script>

<template>
    <div class="bg-white border rounded px-4 shadow mb-3 pt-3">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center  gap-2">
                <a href="javascript:void(0)">
                    <img :src="authUser.avatar_url" class="w-[40px] rounded-full border border-2 transition-all hover:border-blue-500" alt=""/>
                </a>
                <div>
                    <h4 class="font-bold">
                        <a href="javascript:void(0)" class="hover:underline">{{authUser.name}}</a> >
                        <template v-if="post.group">
                            <a href="javascript:void(0)" class="hover:underline">
                                {{post.group.name}}
                            </a>
                        </template>
                    </h4>
                    <small class="text-gray-400">{{post.created_at}}</small>
                </div>
            </div>
            <EditDeleteDropdown :user="post.user" @edit="openEditModal" @delete="deletePost"/>
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
            <DisclosurePanel class="mt-3">
                    <div class="flex items-center  gap-2 mb-3">
                        <a href="javascript:void(0)">
                            <img :src="authUser.avatar_url" class="w-[40px] rounded-full border border-2 transition-all
                            hover:border-blue-500" alt=""/>
                        </a>
                        <div class="flex flex-1">
                            <InputTextArea v-model="newCommentText" placeholder="Enter your comment here" rows="1"
                                           class="w-full max-h-[150] resize-none"></InputTextArea>
                            <IndigoButton @click="createComment" class="rounded-l-none w-[100px] max-w-[100px]">Submit</IndigoButton>
                        </div>
                    </div>

                <div>
                    <div v-for="comment of post.comments" :key="comment.id" class="mb-4">
                        <div class="flex justify-between   gap-2">
                            <div class="flex gap-2">
                                <a href="javascript:void(0)">
                                    <img :src="comment.user.avatar_url" class="w-[40px] rounded-full border border-2 transition-all
                            hover:border-blue-500" alt=""/>
                                </a>
                                <div>
                                    <h4 class="font-bold">
                                        <a href="javascript:void(0)" class="hover:underline">{{comment.user.name}}</a>

                                    </h4>
                                    <small class="text-xs text-gray-400">{{comment.updated_at}}</small>
                                </div>
                            </div>
                            <EditDeleteDropdown :user="comment.user" @edit="startEditComment(comment)" @delete="deleteComment(comment)"/>
                        </div>


                        <div v-if="editingComment && editingComment.id === comment.id" class=" ml-12">
                            <InputTextArea v-model="editingComment.comment" placeholder="Enter your comment here" rows="1"
                                           class="w-full max-h-[150] resize-none"></InputTextArea>
                            <div class="flex gap-2 justify-end">
                                <button @click="editingComment=null" class="text-indigo-500">cancel</button>
                                <IndigoButton @click="updateComment" class=" w-[100px] max-w-[100px]">Update</IndigoButton>

                            </div>
                        </div>

                        <ReadMoreReadLess v-else :content="comment.comment" />


<!--                        <div class="fex flex-1 ml-10" v-html="comment.comment">-->
<!--                        </div>-->
                    </div>
                </div>

            </DisclosurePanel>
        </Disclosure>


    </div>
</template>

<style>

</style>
