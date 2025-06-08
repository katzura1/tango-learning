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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
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
type User = {
    id: number;
    name: string;
    email: string;
    role: string;
};

// Page Data
const breadcrumbItems: BreadcrumbItem[] = [{ title: 'User Management', href: '/users' }];
const users = ref<User[]>([]);
const availableRoles = [
    { value: 'user', label: 'User' },
    { value: 'admin', label: 'Admin' },
];

// Modal and Dialog States
const isEdit = ref(false);
const showModal = ref(false);
const showDeleteDialog = ref(false);
const userToDelete = ref<User | null>(null);

// Form State
const form = useForm({
    id: null as number | null,
    name: '',
    email: '',
    role: '',
    password: '',
});

// Table States
const globalFilter = ref('');
const sorting = ref([]);
const isLoading = ref(false);

// Table Columns Definition
const baseColumns: ColumnDef<User>[] = [
    {
        accessorKey: 'name',
        header: 'Name',
        cell: ({ row }) => row.getValue('name'),
        enableSorting: true,
        // size: 'auto' as unknown as number,
    },
    {
        accessorKey: 'email',
        header: 'Email',
        cell: ({ row }) => row.getValue('email'),
        enableSorting: true,
        // size: 'auto' as unknown as number,
    },
    {
        accessorKey: 'role',
        header: 'Role',
        cell: ({ row }) => row.getValue('role'),
        enableSorting: true,
        // size: 'auto' as unknown as number,
    },
    {
        id: 'actions',
        header: 'Actions',
        cell: ({ row }) => {
            const user = row.original;
            return h('div', { class: 'flex gap-2' }, [
                h(
                    Button,
                    {
                        size: 'sm',
                        variant: 'outline',
                        onClick: () => openEdit(user),
                    },
                    () => 'Edit',
                ),
                h(
                    Button,
                    {
                        size: 'sm',
                        variant: 'destructive',
                        onClick: () => deleteUser(user),
                    },
                    () => 'Delete',
                ),
            ]);
        },
        enableSorting: false,
        size: 100,
    },
];

const columns: ColumnDef<User>[] = [
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
        return users.value;
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
async function fetchUsers() {
    isLoading.value = true; // Set loading true sebelum fetch
    // delay 5 detik
    await new Promise((resolve) => setTimeout(resolve, 1000));

    axios
        .get('/users/list')
        .then((res) => {
            users.value = res.data;
            // simulasi ada 1000 data
            users.value = Array.from({ length: 1000 }, (_, i) => ({
                id: i + 1,
                name: `User ${i + 1}`,
                email: `user${i + 1}`,
                role: i % 2 === 0 ? 'user' : 'admin',
            }));
        })
        .catch((error) => {
            console.error('Failed to fetch users:', error);
        })
        .finally(() => {
            isLoading.value = false; // Set loading false setelah fetch selesai
        });
}

// Form Functions
function openAdd() {
    isEdit.value = false;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEdit(user: User) {
    console.log(user);
    form.clearErrors();
    isEdit.value = true;
    showModal.value = true;
    form.id = user.id;
    form.name = user.name;
    form.email = user.email;
    form.role = user.role;
    form.password = '';
}

function closeForm() {
    showModal.value = false;
    form.reset();
}

function submit() {
    if (isEdit.value && form.id) {
        form.put(`/users/${form.id}`, {
            onSuccess: () => {
                fetchUsers();
                closeForm();
                toast.success('User has been updated successfully');
            },
            onError: () => {
                toast.error('Failed to update user');
            },
        });
    } else {
        form.post('/users', {
            onSuccess: () => {
                fetchUsers();
                closeForm();
                toast.success('New user has been added successfully');
            },
            onError: () => {
                toast.error('Failed to add user');
            },
        });
    }
}

// Delete Dialog Functions
function openDeleteDialog(user: User) {
    userToDelete.value = user;
    showDeleteDialog.value = true;
}

function closeDeleteDialog() {
    showDeleteDialog.value = false;
    userToDelete.value = null;
}

function confirmDelete() {
    if (userToDelete.value) {
        axios
            .delete(`/users/${userToDelete.value.id}`)
            .then(() => {
                fetchUsers();
                closeDeleteDialog();
                toast.success(`User ${userToDelete.value?.name} has been deleted successfully`);
            })
            .catch((error) => {
                toast.error('Failed to delete user');
                console.error('Delete error:', error);
            });
    }
}

function deleteUser(user: any) {
    openDeleteDialog(user);
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
onMounted(fetchUsers);
</script>

<template>
    <Head title="User Management" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col space-y-6 px-4 py-6">
            <div class="align-center flex items-center justify-between">
                <HeadingSmall title="Users" description="Manage user accounts for your application." />

                <div class="flex justify-end">
                    <Button @click="openAdd">Add User</Button>
                    <Button variant="outline" class="ml-2" @click="fetchUsers">Refresh</Button>
                </div>
            </div>

            <!-- User Modal Form -->
            <Dialog :open="showModal" @update:open="showModal = $event">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>{{ isEdit ? 'Edit User' : 'Add User' }}</DialogTitle>
                    </DialogHeader>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label for="name" class="mb-2 block">Name</Label>
                            <Input id="name" v-model="form.name" required placeholder="Name" />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div>
                            <Label for="email" class="mb-2 block">Email</Label>
                            <Input id="email" v-model="form.email" required type="email" placeholder="Email" />
                            <InputError :message="form.errors.email" />
                        </div>
                        <div>
                            <Label for="role" class="mb-2 block">Role</Label>
                            <!-- Ganti input text dengan select dropdown -->
                            <Select v-model="form.role" required>
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select role" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="role in availableRoles" :key="role.value" :value="role.value">
                                        {{ role.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.role" />
                        </div>
                        <div>
                            <Label for="password" class="mb-2 block"> Password <span v-if="isEdit">(isi jika mau ganti)</span> </Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                :required="!isEdit"
                                type="password"
                                autocomplete="new-password"
                                placeholder="Password"
                            />
                            <InputError :message="form.errors.password" />
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
                            Are you sure you want to delete user <strong>{{ userToDelete?.name }}</strong
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

            <!-- Table User -->
            <!-- Search Input -->
            <div class="flex justify-end py-4">
                <Input placeholder="Search..." class="max-w-sm" v-model="globalFilter" />
            </div>

            <!-- Table User -->
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead
                                v-for="column in table.getAllColumns()"
                                :key="column.id"
                                :class="[
                                    column.getCanSort() ? 'cursor-pointer select-none' : '',
                                    column.id === 'actions' ? 'w-[200px] max-w-[200px]' : '',
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
                                    <!-- Perbaikan: Gunakan FlexRender sebagai component -->
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
