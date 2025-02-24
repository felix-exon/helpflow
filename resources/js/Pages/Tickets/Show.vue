<script setup lang="ts">

import AppLayout from "@/Layouts/AppLayout.vue";
import {useForm} from "@inertiajs/vue3";
import { ref } from 'vue'
import { CheckCircleIcon } from '@heroicons/vue/24/solid'
import {
    FaceFrownIcon,
    FaceSmileIcon,
    FireIcon,
    HandThumbUpIcon,
    HeartIcon,
    PaperClipIcon,
    XMarkIcon,
} from '@heroicons/vue/20/solid'
import { Listbox, ListboxButton, ListboxLabel, ListboxOption, ListboxOptions } from '@headlessui/vue';

const props = defineProps<{
    ticket: Ticket;
}>();

const form = useForm<{
    ticket_id: number;
    content: string;
}>({
    ticket_id: props.ticket.id,
    content: ''
});

const commitComment = () => {
    form.post(route('tickets.comment', props.ticket.id), {
        onSuccess: () => {
            form.reset();
        }
    })
};

</script>

<template>

    <AppLayout>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                    <div class="p-4">
                        {{ ticket.title }}
                        <div class="mt-1 text-sm">
                            {{ ticket.description }}
                        </div>
                    </div>


                    <div class="border-b border-gray-900/10 pb-4" />

                    <div class="p-4">

                    <div>
                        Activity
                    </div>

                    <div class="pt-4 ">
                        <ul role="list" class="space-y-6 max-h-[500px] overflow-y-scroll pr-2">
                            <li v-for="(comment, commentIndex) in ticket.comments" :key="comment.id" class="relative flex gap-x-4">
                                <div :class="[commentIndex ===  ticket.comments.length - 1 ? 'h-6' : '-bottom-6', 'absolute left-0 top-0 flex w-6 justify-center']">
                                    <div class="w-px bg-gray-200" />
                                </div>

                                    <img :src="comment.creator.imageUrl ?? 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80'" alt="" class="relative mt-3 size-6 flex-none rounded-full bg-gray-50" />
                                    <div class="flex-auto rounded-md p-3 ring-1 ring-inset ring-gray-200">
                                        <div class="flex justify-between gap-x-4">
                                            <div class="py-0.5 text-xs/5 text-gray-500">
                                                <span class="font-medium text-gray-900">{{ comment.creator.name }}</span> commented
                                            </div>
                                            <time :datetime="comment.created_at" class="flex-none py-0.5 text-xs/5 text-gray-500">{{ comment.created_at }}</time>
                                        </div>
                                        <p class="text-sm/6 text-gray-500">{{ comment.content }}</p>
                                    </div>

                            </li>
                        </ul>
                        <!-- New comment form -->
                        <div class="pt-6 flex gap-x-3 border-t border-gray-200 ">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="" class="size-6 flex-none rounded-full bg-gray-50" />
                            <form @submit.prevent class="relative flex-auto">
                                <div class="overflow-hidden rounded-lg pb-12 outline outline-1 -outline-offset-1 outline-gray-300 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                                    <label for="comment" class="sr-only">Add your comment</label>
                                    <textarea v-model="form.content" rows="2" name="comment" id="comment" class="block w-full resize-none bg-transparent px-3 py-1.5 text-base text-gray-900 placeholder:text-gray-400 focus:outline focus:outline-0 sm:text-sm/6" placeholder="Add your comment..." />
                                </div>

                                <div class="absolute inset-x-0 bottom-0 flex justify-between py-2 pl-3 pr-2">
                                    <button type="button"
                                            :disabled="form.processing"
                                            @click="commitComment" class="rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Comment</button>
                                </div>
                            </form>
                        </div>
                    </div>


                    </div>

                </div>
            </div>
        </div>

    </AppLayout>

</template>

<style scoped>

</style>
