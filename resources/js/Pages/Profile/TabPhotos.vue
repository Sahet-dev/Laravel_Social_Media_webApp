<script setup>
import {ArrowDownTrayIcon} from '@heroicons/vue/24/outline'
import {isImage} from '@/helpers.js'
import {PaperClipIcon} from "@heroicons/vue/24/solid/index.js";
import {ref} from "vue";
import AttachmentPreviewModal from "@/Components/app/AttachmentPreviewModal.vue";
//, isVideo

const currentPhotoIndex = ref(0)
const showModal = ref(false)
defineProps({
    photos: Array
})

defineEmits(['attachmentClick'])



function openPhoto(index) {
    currentPhotoIndex.value = index;
    showModal.value = true;
}

</script>
<template>
    <div class="grid gap-2 grid-cols-2 sm:grid-cols-3">
        <template v-for="(attachment, ind) of photos">

            <div @click="openPhoto(ind)"
                 class="group aspect-square bg-blue-100 flex flex-col items-center justify-center text-gray-500 relative cursor-pointer">

                <!-- Download-->
                <a @click.stop :href="route('post.download', attachment)"
                   class="z-20 opacity-0 group-hover:opacity-100 transition-all w-8 h-8 flex items-center justify-center text-gray-100 bg-gray-700 rounded absolute right-2 top-2 cursor-pointer hover:bg-gray-800">
                    <ArrowDownTrayIcon class="w-4 h-4"/>
                </a>
                <!--/ Download-->

                <img v-if="isImage(attachment)"
                     :src="attachment.url"
                     class="object-contain aspect-square"/>
                <div v-else-if="isVideo(attachment)" class="relative flex justify-center items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor"
                         class="z-20 absolute w-16 h-16 text-gray-800 text-white opacity-70">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.91 11.672a.375.375 0 010 .656l-5.603 3.113a.375.375 0 01-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112z"/>
                    </svg>

                    <div class="absolute left-0 top-0 w-full h-full bg-black/50 z-10"></div>
                    <video :src="attachment.url"></video>
                </div>
                <div v-else class="flex flex-col justify-center items-center">
                    <PaperClipIcon class="w-10 h-10 mb-3"/>

                    <small>{{ attachment.name }}</small>
                </div>
            </div>
        </template>
    </div>
    <div v-if="!photos.length" class="py-8 text-center text-gray-500">No photos were found</div>

    <AttachmentPreviewModal :attachments="photos || []"
                            v-model:index="currentPhotoIndex"
                            v-model="showModal"/>
</template>
