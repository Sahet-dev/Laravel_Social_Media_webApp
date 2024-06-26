<script setup>
import {computed, onMounted, reactive, ref, watch} from 'vue'
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from '@headlessui/vue'
import InputTextArea from "@/Components/app/InputTextArea.vue";
import PostUserHeader from "@/Components/app/PostUserHeader.vue";
import { XMarkIcon, PaperClipIcon, BookmarkIcon, ArrowUturnLeftIcon, SparklesIcon } from '@heroicons/vue/24/solid'
import {useForm, usePage} from "@inertiajs/vue3";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import {isImage} from "@/helpers.js";
import axiosClient from "@/axiosClient.js";

const editor = ClassicEditor;

const editorConfig = {
    toolbar: ['heading', '|', 'bold', 'italic', '|', 'link', 'numberedList', '|', 'outdent', 'indent', '|', 'bulletList', '|', 'blockQuote']
}

const  props = defineProps({
    post: {
        type: Object,
        required: true
    },
    group: {
        type: Object,
        default: null
    },
    modelValue: Boolean,
})

const attachmentExtensions = usePage().props.attachmentExtensions;

/**
 * {
 *     file: File,
 *     url: '',
 * }
 * @type {Ref<UnwrapRef<*[]>>}
 */
const attachhmentFiles = ref([]);
const attachmentErrors = ref([])
const formErrors = ref({})
const aiButtonLoading = ref(false)


const form = useForm({
    body: '',
    attachments: [],
    group_id: null,
    deleted_file_ids: [],
    _method: 'POST'
})


const show = computed({
    get: ()=> props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const emit = defineEmits(['update:modalValue', 'hide'])

watch(()=> props.post, ()=>{
        form.body = props.post.body || ''
});



function closeModal() {
    show.value = false
    emit('hide')
    resetModal()
    // emit('update:modalValue', false)
}

function resetModal(){
    form.reset()
    formErrors.value = {}
    attachhmentFiles.value = []
    attachmentErrors.value = []
    if (props.post.attachments) {
        props.post.attachments.forEach(file => file.deleted = false)
    }
}

const allowedExtensions = attachmentExtensions

function submit(){
    if (props.group){
        form.group_id = props.group.id
    }
    form.attachments = attachhmentFiles.value.map(myFile => myFile.file)
    if (props.post.id){
        form._method = 'PUT' //Post method with PUT needed when you update post and send some file
        form.post(route('post.update', props.post.id),{
            preserveScroll: true,
            onSuccess: (res)=>{
                closeModal()
            },
            onError: (errors) => {
                processErrors(errors)
            }
        })
    }else {
        form.post(route('post.create'), {
            preserveScroll: true,
            onSuccess: (res) => {
                closeModal()
            },
            onError: (errors) => {
                processErrors(errors)
            }
        })
    }
}

const computedAttachments = computed(()=> {
    return [...attachhmentFiles.value, ...(props.post?.attachments || [])];


})

const showExtensionsText = computed(()=>{
    for (let myFile of attachhmentFiles.value){
        const file = myFile.file
        let parts  = file.name.split('.')
        let ext = parts.pop().toLowerCase()
        if (!attachmentExtensions.includes(ext)){
            return true;
        }
    }
    return false;
})

function processErrors(errors){
    formErrors.value = errors
    for (const key in errors){
        if (key.includes('.')){
            const [, index] = key.split('.')
            attachmentErrors.value[index] = errors[key]
        }
    }
}
async function onAttachmentChoose($event) {
    for (const file of $event.target.files) {

        const myFile = {
            file,
            url: await readFile(file)
        };
        attachhmentFiles.value.push(myFile);
    }
    $event.target.value = null;
}

async function readFile(file){
    return new Promise((res, rej)=>{
        if (isImage(file)) {
            const reader = new FileReader();
            reader.onload = ()=> {
                res(reader.result)
            }
            reader.onerror = rej
            reader.readAsDataURL(file)
        } else {
            res(null)
        }
    })

}

function removeFile(myFile){
    if(myFile.file){
        attachhmentFiles.value = attachhmentFiles.value.filter(f => f !== myFile)
    } else {
        form.deleted_file_ids.push(myFile.id)
        myFile.deleted = true
    }
}

function undoDelete(myFile){
    myFile.deleted = false;
    form.deleted_file_ids = form.deleted_file_ids.filter(id => myFile.id !== id);
}
function getAiPost(){
    if (!form.body){
        return;
    }
    aiButtonLoading.value = true
    axiosClient.post(route('post.aiContent'), {
        prompt: form.body
    })
        .then(({data}) => {
        form.body = data.content
    aiButtonLoading.value = false

        console.log(data)
    })
        .catch(err => {
            console.log(err)
    aiButtonLoading.value = false

        })
}
</script>

<template>
    <teleport to="body">
        <TransitionRoot appear :show="show" as="template">
            <Dialog as="div" @close="closeModal" class="relative z-50">
                <TransitionChild
                    as="template"
                    enter="duration-300 ease-out"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="duration-200 ease-in"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-black/25" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto">
                    <div
                        class="flex min-h-full items-center justify-center p-4 text-center"
                    >
                        <TransitionChild
                            as="template"
                            enter="duration-300 ease-out"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="duration-200 ease-in"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                        >
                            <DialogPanel
                                class="w-full max-w-md transform overflow-hidden rounded bg-white
                                text-left align-middle shadow-xl transition-all"
                            >
                                <DialogTitle
                                    as="h3"
                                    class="flex items-center justify-between py-3 px-4 font-medium bg-gray-100 text-gray-900"
                                >
                                    {{ post.id ?  'Update post' : 'Create Post' }}
                                    <button @click="closeModal" class="w-8 h-8 rounded-full hover:bg-black/5 transition flex items-center
                                        justify-center">
                                        <XMarkIcon class="w-4 h-4"/>
                                    </button>
                                </DialogTitle>
                                <div class="p-3 ">
                                    <PostUserHeader :post="post" :show-time="false" class="mb-4"/>

                                    <div v-if="formErrors.group_id" class="bg-red-400 py-2 px-3 rounded text-white mb-3">
                                        {{formErrors.group_id}}
                                    </div>

                                    <div class="relative group">
                                        <ckeditor :editor="editor" v-model="form.body" :config="editorConfig"></ckeditor>

                                        <button
                                            @click="getAiPost"
                                            :disabled="aiButtonLoading"
                                            class="absolute right-1 top-12 w-8 h-8 p-1 rounded bg-indigo-500 hover:bg-indigo-600 text-white flex justify-center items-center transition-all opacity-0  group-hover:opacity-100 disabled:cursor-not-allowed disabled:bg-indigo-400 disabled:hover:bg-indigo-400">
                                            <svg v-if="aiButtonLoading" class="animate-spin h-4 w-4 text-white"
                                                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                        stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>

                                            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/>
                                            </svg>

                                        </button>
                                    </div>
                                        <div v-if="showExtensionsText" class="border-l-4 border-amber-500 py-2 px-3 bg-amber-100 mt-3
                                        text-gray-800">
                                            Supported files:<br>
                                            <small>{{ attachmentExtensions.join(', ')}}</small>
                                        </div>
                                    <div v-if="formErrors.attachments" class="border-l-4 border-red-500 py-2 px-3 bg-red-100 mt-3
                                        text-gray-800">
                                        {{formErrors.attachments}}
                                    </div>

                                    <div class="grid  gap-3 my-3 " :class="[
                                        computedAttachments.length === 1 ? 'grid-cols-1' : 'grid-cols-2'
                                    ]">
                                        <div v-for="(myFile, ind) of computedAttachments" class="">
                                            <div class="group aspect-square items-center justify-center bg-blue-100
                                                flex flex-col items-center justify-center text-gray-500 relative border-2" :class="attachmentErrors[ind] ? 'border-red-500' : ''">
                                                <div v-if="myFile.deleted" class="absolute z-10 left-0 bottom-0 right-0 py-2 px-3 text-sm
                                                bg-black text-white flex justify-between">
                                                    Revert
                                                    <ArrowUturnLeftIcon @click="undoDelete(myFile)" class="w-4 h-4 cursor-pointer" />
                                                </div>
                                                <button @click="removeFile(myFile)" class="absolute right-3 top-3 w-7 h-7 flex items-center
                                                justify-center bg-black/30
                                                    text-white rounded-full hover:bg-gray-400">
                                                    <XMarkIcon class="w-5 h-5 "/>

                                                </button>
                                                <img v-if="isImage(myFile.file  || myFile)" :src="myFile.url"
                                                     class="object-contain" :class="myFile.deleted ? 'opacity-50' : ''">
                                                <div v-else class="flex flex-col justify-center items-center px-3" :class="myFile.deleted ? 'opacity-50' : ''">
                                                   <PaperClipIcon class="w-12 h-12 mb-3"/>
                                                    <small  class="text-center ">
                                                        {{(myFile.file || myFile).name}}
                                                    </small>
                                                </div>
                                            </div>
                                            <small class="text-red-500" v-if="attachmentErrors[ind]">{{ attachmentErrors[ind] }}</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-2 py-3 px-4">
                                    <button
                                        type="button"
                                        class="flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2
                                             text-sm font-semibold text-white
                                            shadow-sm hover:bg-indigo-500 focus-visible:outline
                                            focus-visible:outline-2 focus-visible:outline-offset-2
                                            focus-visible:outline-indigo-600 w-full relative"
                                        @click="submit"
                                    >
                                        <PaperClipIcon class="w-4 h-4 mr-2"/>Attach Files
                                        <input @click.stop @change="onAttachmentChoose" type="file" multiple class="absolute left-0 top-0 right-0 bottom-0 opacity-0">
                                    </button>
                                    <button
                                        type="button"
                                        class="flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2
                                             text-sm font-semibold text-white
                                            shadow-sm hover:bg-indigo-500 focus-visible:outline
                                            focus-visible:outline-2 focus-visible:outline-offset-2
                                            focus-visible:outline-indigo-600 w-full"
                                        @click="submit"
                                    >
                                        <BookmarkIcon class="w-4 h-4 mr-2"/>Submit
                                    </button>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </teleport>
</template>

