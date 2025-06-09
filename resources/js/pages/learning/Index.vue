<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Book } from 'lucide-vue-next';

interface Category {
    id: number;
    name: string;
    description: string;
    slug: string;
    vocabularies_count: number;
}

interface Props {
    categories: Category[];
}

defineProps<Props>();

// Breadcrumb items
const breadcrumbItems = [{ title: 'Learning', href: '/learning' }];

// Category card color based on category name (for visual distinction)
const getCategoryColor = (name: string) => {
    const colors = [
        'bg-red-100 dark:bg-red-950',
        'bg-blue-100 dark:bg-blue-950',
        'bg-green-100 dark:bg-green-950',
        'bg-yellow-100 dark:bg-yellow-950',
        'bg-purple-100 dark:bg-purple-950',
        'bg-pink-100 dark:bg-pink-950',
        'bg-indigo-100 dark:bg-indigo-950',
        'bg-teal-100 dark:bg-teal-950',
        'bg-orange-100 dark:bg-orange-950',
    ];

    // Simple hash function to pick a color based on name
    const index = name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0) % colors.length;
    return colors[index];
};
</script>

<template>
    <Head title="Learning" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6">
            <div class="mb-6 flex items-center justify-between">
                <HeadingSmall
                    title="Japanese Vocabulary Learning"
                    description="Explore different vocabulary categories to enhance your Japanese language skills."
                />
            </div>

            <!-- Categories Grid -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="category in categories"
                    :key="category.id"
                    :class="getCategoryColor(category.name)"
                    class="justify-between transition-all duration-300 hover:scale-[1.02] hover:shadow-lg"
                >
                    <CardHeader>
                        <CardTitle>{{ category.name }}</CardTitle>
                        <CardDescription>{{ category.description }}</CardDescription>
                    </CardHeader>

                    <CardContent>
                        <div class="flex items-center gap-2">
                            <Book class="h-5 w-5" />
                            <span>{{ category.vocabularies_count }} vocabulary words</span>
                        </div>
                    </CardContent>

                    <CardFooter>
                        <Button class="w-full" :href="`/learning/${category.slug}`" as="a"> Start Learning </Button>
                    </CardFooter>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-if="categories.length === 0" class="py-12 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                    <Book class="h-8 w-8 text-gray-400" />
                </div>
                <h3 class="mb-1 text-lg font-medium">No categories found</h3>
                <p class="text-gray-500">There are no vocabulary categories available yet.</p>
            </div>
        </div>
    </AppLayout>
</template>
