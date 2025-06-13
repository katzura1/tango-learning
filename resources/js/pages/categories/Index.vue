<script setup lang="ts">
// Vue and Core Imports
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { h, onMounted, ref } from 'vue';

// Layout and UI Components
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';

// Icons
import { ChevronDown, ChevronUp } from 'lucide-vue-next';

// Toast
import { toast } from 'vue-sonner';

// Table Imports
import {
    ColumnDef,
    FlexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useVueTable,
} from '@tanstack/vue-table';

// Types
import { type BreadcrumbItem } from '@/types';

// Type Definitions
type Category = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
};

// Page Data
const breadcrumbItems: BreadcrumbItem[] = [{ title: 'Category Management', href: '/categories' }];
const categories = ref<Category[]>([]);

// Modal and Dialog States
const isEdit = ref(false);
const showModal = ref(false);
const showDeleteDialog = ref(false);
const categoryToDelete = ref<Category | null>(null);

// Form State
const form = useForm({
    id: null as number | null,
    name: '',
    description: '',
    is_active: true,
});

// Table States
const globalFilter = ref('');
const sorting = ref([]);
const isLoading = ref(false);

// Table Columns Definition
const baseColumns: ColumnDef<Category>[] = [
    {
        accessorKey: 'name',
        header: 'Name',
        cell: ({ row }) => row.getValue('name'),
        enableSorting: true,
    },
    {
        accessorKey: 'slug',
        header: 'Slug',
        cell: ({ row }) => row.getValue('slug'),
        enableSorting: true,
    },
    {
        accessorKey: 'description',
        header: 'Description',
        cell: ({ row }) => row.getValue('description') || '-',
        enableSorting: true,
    },
    {
        id: 'actions',
        header: 'Actions',
        cell: ({ row }) => {
            const category = row.original;
            return h('div', { class: 'flex gap-2' }, [
                h(
                    Button,
                    {
                        size: 'sm',
                        variant: 'outline',
                        onClick: () => openEdit(category),
                    },
                    () => 'Edit',
                ),
                h(
                    Button,
                    {
                        size: 'sm',
                        variant: 'destructive',
                        onClick: () => deleteCategory(category),
                    },
                    () => 'Delete',
                ),
            ]);
        },
        enableSorting: false,
        size: 100,
    },
];

const columns: ColumnDef<Category>[] = [
    {
        id: 'number',
        header: 'No.',
        cell: ({ row }) => {
            // For now, just show the row index + 1 (we'll update this later)
            return row.index + 1;
        },
        enableSorting: false,
        size: 60,
    },
    ...baseColumns,
];

// Table Initialization
const table = useVueTable({
    get data() {
        return categories.value;
    },
    columns,
    state: {
        get globalFilter() {
            return globalFilter.value;
        },
        get sorting() {
            return sorting.value;
        },
    },
    onGlobalFilterChange: setGlobalFilter,
    onSortingChange: setSorting,
    getCoreRowModel: getCoreRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    initialState: {
        pagination: {
            pageSize: 10,
        },
    },
    defaultColumn: {
        size: 0, //starting column size
        minSize: 0, //enforced during column resizing
    },
});

columns[0].cell = ({ row }) => {
    const pageIndex = table.getState().pagination.pageIndex;
    const pageSize = table.getState().pagination.pageSize;
    return pageIndex * pageSize + row.index + 1;
};

// API Functions
async function fetchCategories() {
    isLoading.value = true;

    axios
        .get('/categories/list')
        .then((res) => {
            categories.value = res.data;
        })
        .catch((error) => {
            console.error('Failed to fetch categories:', error);
        })
        .finally(() => {
            isLoading.value = false;
        });
}

// Form Functions
function openAdd() {
    isEdit.value = false;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEdit(category: Category) {
    form.clearErrors();
    isEdit.value = true;
    showModal.value = true;
    form.id = category.id;
    form.name = category.name;
    form.description = category.description || '';
}

function closeForm() {
    showModal.value = false;
    form.reset();
}

function submit() {
    if (isEdit.value && form.id) {
        form.put(`/categories/${form.id}`, {
            onSuccess: () => {
                fetchCategories();
                closeForm();
                toast.success('Category has been updated successfully');
            },
            onError: () => {
                toast.error('Failed to update category');
            },
        });
    } else {
        form.post('/categories', {
            onSuccess: () => {
                fetchCategories();
                closeForm();
                toast.success('New category has been added successfully');
            },
            onError: () => {
                toast.error('Failed to add category');
            },
        });
    }
}

// Delete Dialog Functions
function openDeleteDialog(category: Category) {
    categoryToDelete.value = category;
    showDeleteDialog.value = true;
}

function closeDeleteDialog() {
    showDeleteDialog.value = false;
    categoryToDelete.value = null;
}

function confirmDelete() {
    if (categoryToDelete.value) {
        axios
            .delete(`/categories/${categoryToDelete.value.id}`)
            .then(() => {
                fetchCategories();
                closeDeleteDialog();
                toast.success(`Category ${categoryToDelete.value?.name} has been deleted successfully`);
            })
            .catch((error) => {
                toast.error('Failed to delete category');
                console.error('Delete error:', error);
            });
    }
}

function deleteCategory(category: Category) {
    openDeleteDialog(category);
}

// Table Helper Functions
function setGlobalFilter(value: string) {
    globalFilter.value = value;
}

function setSorting(updater: any) {
    if (typeof updater === 'function') {
        sorting.value = updater(sorting.value);
    } else {
        sorting.value = updater;
    }
}

// Lifecycle Hooks
onMounted(fetchCategories);
</script>

<template>
    <Head title="Category Management" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col space-y-6 px-4 py-6">
            <div class="align-center flex flex-col items-center justify-end gap-3">
                <HeadingSmall class="w-full" title="Categories" description="Manage categories for your application." />

                <div class="flex w-full justify-end">
                    <Button @click="openAdd">Add Category</Button>
                    <Button variant="outline" class="ml-2" @click="fetchCategories">Refresh</Button>
                </div>
            </div>

            <!-- Category Modal Form -->
            <Dialog :open="showModal" @update:open="showModal = $event">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>{{ isEdit ? 'Edit Category' : 'Add Category' }}</DialogTitle>
                    </DialogHeader>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label for="name" class="mb-2 block">Name</Label>
                            <Input id="name" v-model="form.name" required placeholder="Category Name" />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div>
                            <Label for="description" class="mb-2 block">Description</Label>
                            <Textarea id="description" v-model="form.description" placeholder="Description (optional)" />
                            <InputError :message="form.errors.description" />
                        </div>
                        <div class="flex justify-end gap-3">
                            <Button type="button" variant="ghost" @click="closeForm">Cancel</Button>
                            <Button type="submit" :disabled="form.processing">{{ isEdit ? 'Update' : 'Save' }}</Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Dialog Konfirmasi Delete -->
            <Dialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Confirm Deletion</DialogTitle>
                    </DialogHeader>
                    <div class="py-4">
                        <p>
                            Are you sure you want to delete category <strong>{{ categoryToDelete?.name }}</strong
                            >?
                        </p>
                        <p class="mt-2 text-sm text-red-600">This action cannot be undone.</p>
                    </div>
                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="ghost" @click="closeDeleteDialog">Cancel</Button>
                        <Button type="button" variant="destructive" @click="confirmDelete">Delete</Button>
                    </div>
                </DialogContent>
            </Dialog>

            <!-- Search Input -->
            <div class="flex justify-end py-4">
                <Input placeholder="Search..." class="max-w-sm" v-model="globalFilter" />
            </div>

            <!-- Table Categories -->
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead
                                v-for="column in table.getAllColumns()"
                                :key="column.id"
                                :class="[
                                    column.getCanSort() ? 'cursor-pointer select-none' : '',
                                    column.id === 'actions' ? 'w-[100px] max-w-[100px]' : '',
                                    column.id === 'number' ? 'w-[60px] max-w-[60px]' : '',
                                ]"
                                @click="column.getCanSort() ? column.toggleSorting() : null"
                            >
                                <div class="flex items-center gap-2">
                                    {{ column.columnDef.header }}
                                    <ChevronUp v-if="column.getIsSorted() === 'asc'" class="h-4 w-4" />
                                    <ChevronDown v-else-if="column.getIsSorted() === 'desc'" class="h-4 w-4" />
                                </div>
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <!-- Loading state -->
                        <template v-if="isLoading">
                            <TableRow v-for="i in 5" :key="`loading-${i}`">
                                <TableCell v-for="column in columns" :key="`loading-cell-${column.id}`" class="py-4">
                                    <div class="h-4 w-3/4 animate-pulse rounded bg-gray-200"></div>
                                </TableCell>
                            </TableRow>
                        </template>
                        <!-- Data rows -->
                        <template v-else>
                            <TableRow v-for="row in table.getRowModel().rows" :key="row.id" :data-state="row.getIsSelected() && 'selected'">
                                <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                    <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="table.getRowModel().rows.length === 0">
                                <TableCell :colSpan="columns.length" class="h-24 text-center"> No results. </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination Controls -->
            <div class="flex items-center justify-end space-x-2 py-4">
                <div class="flex-1 text-sm text-muted-foreground">
                    Page {{ table.getState().pagination.pageIndex + 1 }} of {{ table.getPageCount() }}
                </div>
                <Button variant="outline" size="sm" :disabled="!table.getCanPreviousPage()" @click="table.previousPage()"> Previous </Button>
                <Button variant="outline" size="sm" :disabled="!table.getCanNextPage()" @click="table.nextPage()"> Next </Button>
            </div>
        </div>
    </AppLayout>
</template>
