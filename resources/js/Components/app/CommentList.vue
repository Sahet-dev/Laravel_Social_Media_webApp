<script setup>

import EditDeleteDropdown from "@/Components/app/EditDeleteDropdown.vue";
import {HandThumbUpIcon} from "@heroicons/vue/20/solid/index.js";
import InputTextArea from "@/Components/app/InputTextArea.vue";
import ReadMoreReadLess from "@/Components/app/ReadMoreReadLess.vue";
import {ChatBubbleLeftEllipsisIcon} from "@heroicons/vue/24/solid/index.js";
import IndigoButton from "@/Components/app/IndigoButton.vue";
import {Link, usePage} from "@inertiajs/vue3";
import {ref} from "vue";
import axiosClient from "@/axiosClient.js";
import {Disclosure, DisclosureButton, DisclosurePanel} from "@headlessui/vue";


const authUser = usePage().props.auth.user

const editingComment = ref(null);


const props = defineProps({
    post: Object,
    data: Object,
    parentComment: {
        type: [Object, null],
        default: null
    }
});

const emit = defineEmits(['commentCreate', 'commentDelete', 'editClick', 'attachmentClick']);

if (!props.data.comments) {
    props.data.comments = [];
}



const newCommentText = ref('');


function createComment(){
    axiosClient.post(route('post.comment.create', props.post), {
        comment: newCommentText.value,
        parent_id: props.parentComment?.id || null
    })
        .then(({data}) => {
            newCommentText.value = ''
            props.data.comments.unshift(data)
            if(props.parentComment){
                props.parentComment.num_of_comments++;
            }else {
                props.post.num_of_comments++;
                emit('commentCreate', data)
            }
        })
        // .catch((error) => {
        //     console.error('Error creating comment:', error);
        // });
}
function startEditComment(comment){
    editingComment.value = {
        id: comment.id,
        comment: comment.comment.replace(/<br\s*\/?>/gi, '\n')
    }
}

function deleteComment(comment){
    if (!window.confirm('Delete Comment?')){
        return false;
    }
    axiosClient.delete(route('post.comment.delete', comment.id))
        .then(({data}) => {
            const commentIndex = props.data.comments.findIndex(c=> c.id === comment.id)
            props.data.comments.splice(commentIndex, 1)
            if(props.parentComment){
                props.parentComment.num_of_comments--;
            }
            props.post.num_of_comments--;
            emit('commentDelete', comment)




        })
        .catch((error) => {
            console.error('Error creating comment:', error);
        });
}


function updateComment(){
    axiosClient.put(route('post.comment.update', editingComment.value.id), editingComment.value)
        .then(({data}) => {
            editingComment.value = null
            props.data.comments = props.data.comments.map((c)=> {
                if (c.id ===data.id){
                    return data
                }
                return c;
            })


        })
        .catch((error) => {
            console.error('Error creating comment:', error);
        });
}


function sendCommentReaction(comment){
    axiosClient.post(route('comment.reaction', comment.id), {
        reaction: 'like'
    })
        .then(({data}) => {
            comment.current_user_has_reaction = data.current_user_has_reaction
            comment.num_of_reactions = data.num_of_reactions;

        })
}

function onCommentCreate(comment) {
    if (props.parentComment){
        props.parentComment.num_of_comments++;
    }
    emit('commentCreate', comment)
}

function  onCommentDelete(comment) {
    if (props.parentComment){
        props.parentComment.num_of_comments--;
    }
    emit('commentDelete', comment)
}
</script>

<template>
    <div class="flex items-center  gap-2 mb-3">
        <Link :href="route('profile', authUser.username)">
            <img :src="authUser.avatar_url" class="w-[40px] rounded-full border border-2 transition-all
                            hover:border-blue-500" alt=""/>
        </Link>
        <div class="flex flex-1">
            <InputTextArea v-model="newCommentText" placeholder="Enter your comment here" rows="1"
                           class="w-full max-h-[150] resize-none"></InputTextArea>
            <IndigoButton @click="createComment" class="rounded-l-none w-[100px] max-w-[100px]">Submit</IndigoButton>
        </div>
    </div>
    <div>
        <div v-for="comment of data.comments" :key="comment.id" class="mb-4">
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
            <ReadMoreReadLess v-else :content="comment.comment" content-class="text-sm flex flex-1" />
            <Disclosure>
                <div class="mt-1 flex gap-2">
                    <button @click="sendCommentReaction(comment)" class=" flex items-center text-indigo-600 p-1  rounded-lg" :class="
                                [comment.current_user_has_reaction ? 'bg-indigo-50 hover:bg-indigo-100' : 'bg-gary-100 hover:bg-indigo-50']">
                        <HandThumbUpIcon class="w-4 h-4 mr-2"/>
                        <span class="mr-2">
                                    {{comment.num_of_reactions}}
                                </span>
                        {{comment.current_user_has_reaction ? 'unlike' : 'like'}}

                    </button>
                    <DisclosureButton @click=""
                            class=" flex items-center text-indigo-600 p-1 hover:bg-indigo-100 rounded-lg">
                        <ChatBubbleLeftEllipsisIcon class="w-4 h-4 mr-2 "/>
                        <span class="mr-2">
                        {{comment.num_of_comments}}
                    </span>
                        replies
                    </DisclosureButton>
                </div>
                <DisclosurePanel class="mt-3 ml-10 h-[400px] overflow-auto">
                    <CommentList
                        :post="post"
                        :data="{comments: comment.comments}"
                        :parent-comment="comment"
                        @comment-create="onCommentCreate"
                        @comment-delete="onCommentDelete"

                    />
                </DisclosurePanel>
            </Disclosure>


        </div>
    </div>
</template>

<style scoped>

</style>
