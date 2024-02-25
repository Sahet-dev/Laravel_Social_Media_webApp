<script setup>

import PostItem from "@/Components/app/PostItem.vue";
import PostModal from "@/Components/app/PostModal.vue";
import {ref} from "vue";
import {usePage} from "@inertiajs/vue3";
import AttachmentPreviewModal from "@/Components/app/AttachmentPreviewModal.vue";

defineProps({
    posts: Array
})
const authUser = usePage().props.auth.user;

const showEditModal = ref(false)
const showAttachmentsModal = ref(false)
const editPost = ref({})
const previewAttachmentPost = ref({})

function openEditModal(post){
    editPost.value = post;
    showEditModal.value = true;

}
function openAttachmenPreviewModal(post, index){
    previewAttachmentPost.value = {
        post,
        index
    };
    showAttachmentsModal.value = true;
}

function onModalHide(){
    editPost.value = {
        id: null,
        body: '',
        user: authUser
    }
}
</script>

<template>
    <div class="overflow-auto">
        <PostItem v-for="post of posts" :key="post.id" :post="post"
                  @editClick="openEditModal"
                  @attachmentClick="openAttachmenPreviewModal"/>
        <PostModal :post="editPost" v-model="showEditModal" @hide="onModalHide"/>
        <AttachmentPreviewModal :attachments="previewAttachmentPost.post?.attachments || []"
                                v-model:index="previewAttachmentPost.index"
                                v-model="showAttachmentsModal"/>

    </div>
</template>

<style scoped>

</style>
