<script setup>
import { Layout } from '@/Layouts'
import { Button, Pagination, Badge, Dropdown, DropdownItem, Modal } from '@/Components'
import { Select, Input, Textarea } from '@/Components/Form'
import { Table, Th, Td } from '@/Components/Tables'
import { Inertia } from '@inertiajs/inertia'
import { useToast } from "@/Composables";
import { useForm } from '@inertiajs/inertia-vue3'
import { ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import { reactive } from 'vue'


const props = defineProps({
    riwayat: {
        type: Object,
        default: () => ({})
    },
    status: String,
    kategori: String,
    keyword: String,
})


const formCari = useForm({
    status: props.status,
    kategori: props.kategori,
    keyword: props.keyword
})

const formUpdate = useForm({
    id: null,
    judul: null,
    dipinjam: null,
    dikembalikan: null,
    catatan: null,
})

const formExport = reactive({
    dari: null,
    sampai: null,
})


function cari() {
    formCari.get(location.href, {
        preserveScroll: true
    })
}

function selesai(id) {
    Inertia.delete(route('peminjaman.selesai', id), {
        onBefore: () => confirm('Konfirmasi pengembalian?'),
        onSuccess: () => useToast()
    })
}

function restore(id) {
    Inertia.patch(route('peminjaman.restore', id), null, {
        onBefore: () => confirm('Konfirmasi pembatalan?'),
        onSuccess: () => useToast()
    })
}

function destroy(id) {
    Inertia.delete(route('peminjaman.destroy', id), {
        onBefore: () => confirm('Riwayat ini akan dihapus permanen'),
        onSuccess: () => useToast()
    })
}

function edit(item) {
    formUpdate.id = item.id
    formUpdate.judul = item.book.judul
    formUpdate.dipinjam = item.dipinjam.split('T')[0]
    formUpdate.dikembalikan = item.dikembalikan.split('T')[0]
    formUpdate.catatan = item.catatan
}

function update() {
    formUpdate.patch(route('peminjaman.update'), {
        onSuccess: () => useToast()
    })
}

function download() {
    location.href = route('peminjaman.export', formExport)
}

</script>

<template>
    <Layout judul="Riwayat Peminjaman">
        <form class="grid grid-cols-1 md:grid-cols-6 gap-2" @submit.prevent="cari">
            <div class="flex space-x-2 md:col-span-4">
                <Select v-model="formCari.status">
                    <option value="0">Semua</option>
                    <option value="success">Selesai</option>
                    <option value="warning">Sedang dipinjam</option>
                </Select>
                <Select v-model="formCari.kategori">
                    <option value="buku">Buku</option>
                    <option value="peminjam">Peminjam</option>
                </Select>
                <Input v-model="formCari.keyword"
                    :placeholder="formCari.kategori == 'buku' ? 'cari judul / nik' : 'cari nama'" />
                <Button type="submit">Cari</Button>
            </div>
            <div class="flex md:justify-end md:col-span-2">

                <Button :icon="ArrowDownTrayIcon" color="secondary" data-bs-toggle="modal"
                    data-bs-target="#export">Export</Button>
            </div>
        </form>

        <Table :items="riwayat.data" class="mt-4 min-h-[25rem]">
            <template #head>
                <Th>No</Th>
                <Th>NIK</Th>
                <Th>Judul</Th>
                <Th>NIK/NIS Peminjam</Th>
                <Th>Nama Peminjam</Th>
                <Th>Dari</Th>
                <Th>Sampai</Th>
                <Th>Catatan</Th>
                <Th>Dipinjam</Th>
                <Th>Dikembalikan</Th>
                <Th>Status</Th>
                <Th>Keterangan</Th>
                <Th>Aksi</Th>
            </template>
            <template #body="{ item, key }">
                <Th>{{ key + riwayat.from }}</Th>
                <Td :text="item.book.nik" />
                <Td :text="item.book.judul" short />
                <Td :text="item.loanable.nis ?? item.loanable.nik" />
                <Td :text="item.loanable.nama" />
                <Td :text="item.dipinjam" is-date />
                <Td :text="item.dikembalikan" is-date />
                <Td :text="item.catatan" />
                <Td :text="item.dibuat" is-time />
                <Td :text="item.dikembalikan_pada" is-time />
                <Td>
                    <Badge color="success" v-if="item.returned_at">Selesai</Badge>
                    <Badge color="warning" v-else>Sedang dipinjam</Badge>
                </Td>
                <Td>
                    <template v-if="item.status.keterangan">
                        <Badge :color="item.status.keterangan.color">{{ item.status.keterangan.pesan }}</Badge>
                    </template>
                    <template v-else>
                        -
                    </template>
                </Td>
                <Td>
                    <Dropdown text="aksi" color="secondary">
                        <DropdownItem text="Selesai" @click="selesai(item.id)" v-if="!item.returned_at" />
                        <DropdownItem text="Batalkan" @click="restore(item.id)" v-if="item.returned_at" />
                        <hr>
                        <DropdownItem text="Edit" @click="edit(item)" data-bs-toggle="modal" data-bs-target="#edit"
                            v-if="!item.returned_at" />
                        <DropdownItem text="Hapus" @click="destroy(item.id)" />
                    </Dropdown>
                </Td>
            </template>
        </Table>

        <div class="space-y-4 md:space-y-0 md:flex md:justify-between items-center mt-4">
            <div class="text-slate-700">
                Menampilkan <b>{{ riwayat.from }}</b> sampai <b>{{ riwayat.to }}</b> dari <b>{{ riwayat.total }}</b>
            </div>
            <Pagination :links="riwayat.links" />
        </div>

        <!-- Modal Edit -->
        <Modal title="Edit Riwayat" id="edit">
            <h2>Judul: <b>{{ formUpdate.judul }}</b></h2>
            <hr>
            <div class="mt-4 space-y-2" v-if="formUpdate.judul">
                <Input v-model="formUpdate.dipinjam" type="date" label="Dari" required />
                <Input v-model="formUpdate.dikembalikan" type="date" :min="formUpdate.dipinjam" label="Sampai"
                    required />
                <Textarea v-model="formUpdate.catatan" label="Catatan" />
            </div>
            <template #footer>
                <Button color="success" @click="update" :disabled="formUpdate.processing">Update</Button>
            </template>
        </Modal>

        <!-- Modal Export -->
        <Modal title="Pilih rentang waktu" id="export">
            <div class="space-y-2">
                <Input type="date" v-model="formExport.dari" label="Dari" required />
                <Input type="date" v-model="formExport.sampai" :min="formExport.dari" label="Sampai" required />
            </div>
            <template #footer>
                <Button :icon="ArrowDownTrayIcon" color="success" @click="download"
                    :disabled="!(formExport.dari && formExport.sampai)">Download</Button>
            </template>
        </Modal>
    </Layout>
</template>