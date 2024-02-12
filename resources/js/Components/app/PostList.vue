<script setup>

import PostItem from "@/Components/app/PostItem.vue";

defineProps({
    posts: Array
})

    const post1 = {
        user: {
            id: 1,
            avatar: 'https://randomuser.me/api/portraits/men/51.jpg',
            name: 'John Lark'
        },
        group: null,
        attachments: [{
            id: 1,
            name: 'test.png',
            url: 'https://picsum.photos/1010',
            mime: 'image/png',
        },
            {
            id: 2,
            name: 'test2.png',
            url: 'https://picsum.photos/900',
            mime: 'image/png',
        },
            {
            id: 3,
            name: 'MyDoc.docx',
            url: '#',
            mime: 'application/msword',
        }],
        body: `<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor
                incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud
                exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure
                dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla
                pariatur.Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia
                deserunt mollit anim id est laborum.</p>
                <p>In voluptate velit esse cillum dolore eu fugiat nulla
                pariatur.Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia
                deserunt mollit anim id est laborum.</p>`,
        created_at: '1-12-2024 12:34'

    };
    const post2 = {
        user: {
            id: 2,
            avatar: 'https://randomuser.me/api/portraits/men/52.jpg',
            name: 'Tony Stank'
        },
        group: {
            id: 1,
            name: 'Laravel Developers'
        },
        body: `<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor
                incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud
                exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure
                dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla
                pariatur.Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia
                deserunt mollit anim id est laborum.</p>
                <p>In voluptate velit esse cillum dolore eu fugiat nulla
                pariatur.Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia
                deserunt mollit anim id est laborum.</p>`,
        created_at: '1-12-2024 12:34'

    }
</script>

<template>
    <div class="overflow-auto">
        <PostItem v-for="post of posts" :key="post.id" :post="post"/>
        <PostItem :post="post1"/>
    </div>
</template>

<style scoped>

</style>
