<script setup>
import {Link} from '@inertiajs/vue3'
    defineProps(
        {
            group: Object,

        }
    )
</script>

<template>
    <div class=" cursor-pointer hover:bg-gray-50">
        <Link :href="route('group.profile', group.slug)" class="flex items-start gap-1 py-2 px-3">

                <img :src="group.thumbnail_url" class="w-[32px] rounded-full"/>


            <div class="flex-1">
                <div class="flex justify-between">
                    <h3 class="font-black text-lg">{{group.name}}</h3>
                </div>
<!--                <div class="text-xs text-gray-500" v-html="group.description"></div>-->
                <span class="text-xs text-gray-500">
                    {{ group.status === 'approved' ? (group.role === 'admin' ? group.role : '' ) : 'not approved'}}


            </span>
            </div>
        </Link>
    </div>
</template>

<style scoped>

</style>
