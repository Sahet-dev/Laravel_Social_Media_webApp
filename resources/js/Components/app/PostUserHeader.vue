<script setup>
import {Link} from "@inertiajs/vue3";
import {ChevronLeftIcon} from "@heroicons/vue/24/solid/index.js";

defineProps({
    post: {Object
    },
    showTime: {
        type: Boolean,
        default: true
    }
})
</script>

<template>
    <div class="flex items-center  gap-2">
        <Link :href="route('profile', post.user.id)">
            <img :src="post.user.avatar_url" class="w-[40px] rounded-full border border-2 transition-all hover:border-blue-500" alt=""/>
        </Link>
        <div>
            <h4 class="font-bold">
                <Link :href="route('profile', post.user.id)" class="hover:underline">{{post.user.name}}</Link>
                <template v-if="post.group">
                    <ChevronLeftIcon  class="w-12 h-12"/>
                    <Link :href="route('profile', post.user.id)" class="hover:underline">
                        {{post.group.name}}
                    </Link>
                </template>
            </h4>
            <small v-if="showTime" :show class="text-gray-400">{{post.updated_at}}</small>
        </div>
    </div>
</template>

<style scoped>

</style>
