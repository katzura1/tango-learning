<!-- filepath: resources/js/pages/learning/Favorites.vue -->
<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ChevronLeft, HeartIcon } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

interface Vocabulary {
    id: number;
    hiragana: string;
    meaning: string;
    romaji: string;
    example: string;
    is_favorited: boolean;
    category: {
        id: number;
        name: string;
        slug: string;
    };
}

interface Props {
    favorites: Vocabulary[];
}

const props = defineProps<Props>();
const favorites = ref<Vocabulary[]>([...props.favorites]);

// Breadcrumb items
const breadcrumbItems = [
    { title: 'Learning', href: '/learning' },
    { title: 'Favorite Vocabularies', href: '/favorites' },
];

async function toggleFavorite(vocabularyId: number) {
    try {
        const response = await axios.post(`/vocabulary/${vocabularyId}/favorite`);

        if (!response.data.favorited) {
            // Remove from list if unfavorited
            favorites.value = favorites.value.filter((v) => v.id !== vocabularyId);
        }

        toast.success(response.data.message);
    } catch (error) {
        console.error('Error toggling favorite:', error);
        toast.error('Failed to update favorite status');
    }
}
</script>

<template>
    <Head title="Favorite Vocabularies" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6">
            <div class="mb-6 flex items-center justify-between">
                <HeadingSmall title="Favorite Vocabularies" description="Your collection of favorite Japanese vocabulary words." />

                <Button as="a" href="/learning" variant="outline" size="sm" class="flex items-center gap-1">
                    <ChevronLeft class="h-4 w-4" />
                    Back to Categories
                </Button>
            </div>

            <!-- Favorites Table -->
            <div v-if="favorites.length > 0">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>No.</TableHead>
                            <TableHead>Hiragana</TableHead>
                            <TableHead>Romaji</TableHead>
                            <TableHead>Arti</TableHead>
                            <TableHead>Kategori</TableHead>
                            <TableHead>Favorite</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(vocab, index) in favorites" :key="vocab.id">
                            <TableCell>{{ index + 1 }}</TableCell>
                            <TableCell class="text-lg font-bold">{{ vocab.hiragana }}</TableCell>
                            <TableCell>{{ vocab.romaji }}</TableCell>
                            <TableCell>{{ vocab.meaning }}</TableCell>
                            <TableCell>
                                <a :href="`/learning/${vocab.category.slug}`" class="text-blue-600 hover:underline dark:text-blue-400">
                                    {{ vocab.category.name }}
                                </a>
                            </TableCell>
                            <TableCell>
                                <Button variant="ghost" size="icon" @click="toggleFavorite(vocab.id)" class="h-8 w-8">
                                    <HeartIcon class="h-5 w-5 fill-red-500 text-red-500" />
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Empty State -->
            <div v-else class="py-12 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                    <HeartIcon class="h-8 w-8 text-gray-400" />
                </div>
                <h3 class="mb-1 text-lg font-medium">No favorite vocabularies</h3>
                <p class="text-gray-500">You haven't added any vocabulary words to your favorites yet.</p>
                <Button as="a" href="/learning" class="mt-4"> Browse Categories </Button>
            </div>
        </div>
    </AppLayout>
</template>
