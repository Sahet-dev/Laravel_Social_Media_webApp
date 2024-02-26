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
import { XMarkIcon, PaperClipIcon, BookmarkIcon, ArrowUturnLeftIcon } from '@heroicons/vue/24/solid'
import {useForm, usePage} from "@inertiajs/vue3";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import {isImage} from "@/helpers.js";

const editor = ClassicEditor;

const editorConfig = {
    toolbar: ['heading', '|', 'bold', 'italic', '|', 'link', 'numberedList', '|', 'outdent', 'indent', '|', 'bulletList', '|', 'blockQuote']
}

const  props = defineProps({
    post: {
        type: Object,
        required: true
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
const showExtensionsText = ref(false)


const form = useForm({
    body: '',
    attachments: [],
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
    attachhmentFiles.value = []
    showExtensionsText.value = false
    attachmentErrors.value = []
    if (props.post.attachments) {
        props.post.attachments.forEach(file => file.deleted = false)
    }
}

const allowedExtensions = attachmentExtensions

function submit(){
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
        form.post(route('post.create'),{
            preserveScroll: true,
            onSuccess: (res)=>{
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

function processErrors(errors){
    for (const key in errors){
        if (key.includes('.')){
            const [, index] = key.split('.')
            attachmentErrors.value[index] = errors[key]
        }
    }
}
async function onAttachmentChoose($event) {
    showExtensionsText.value = false;
    for (const file of $event.target.files) {
        let parts  = file.name.split('.')
        let ext = parts.pop().toLowerCase()
        if (!attachmentExtensions.includes(ext)){
            showExtensionsText.value = true;
        }
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
                                    <ckeditor :editor="editor" v-model="form.body" :config="editorConfig"></ckeditor>
                                        <div v-if="showExtensionsText" class="border-l-4 border-amber-500 py-2 px-3 bg-amber-100 mt-3 text-gray-800">
                                            Supported files:<br>
                                            <small>{{ attachmentExtensions.join(', ')}}</small>
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

