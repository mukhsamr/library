<script setup>
import { Layout } from '@/Layouts';
import { Button, List, Modal, Badge } from '@/Components';
import { Datalist } from '@/Components/Form';
import { Table, Th, Td } from '@/Components/Tables';
import { useForm } from '@inertiajs/inertia-vue3';
import { CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/outline'
import { isEmpty } from "lodash";

const props = defineProps({
    buku: {
        type: Array,
        default: () => []
    },
    detail: Object,
    nik: String,
    riwayat: {
        type: Array,
        default: () => []
    }
})

const sampul = props.detail?.sampul ?? 'contoh.png'

const formCari = useForm({
    nik: props.nik
})


function cariBuku() {
    formCari.get(route('buku.detail'))
}

</script>

<template>

    <Layout judul="Detail Buku">
        <form class="md:w-1/2 flex space-x-2 items-center" @submit.prevent="cariBuku">
            <Datalist v-model="formCari.nik" placeholder="cari nik / judul / scan barcode" autofocus>
                <option v-for="item in buku" :value="item.nik">{{ item.judul }}</option>
            </Datalist>
            <Button type="submit" :disabled="formCari.processing">Cari</Button>
        </form>

        <hr class="my-3">

        <div class="grid grid-cols-1 md:grid-cols-6 gap-4" v-if="!isEmpty(detail)">
            <div class="md:col-span-2">
                <div class="border p-2">
                    <img :src="`/storage/sampul/${sampul}`" alt="sampul buku">
                </div>
            </div>
            <div class="md:col-span-4">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex space-x-2 items-center">
                        <strong>
                            Tersedia: {{ detail.jumlah_buku - detail.loans_count + ' / ' + detail.jumlah_buku }}
                        </strong>
                        <CheckCircleIcon class="h-5 w-5 text-green-600"
                            v-if="detail.jumlah_buku - detail.loans_count" />
                        <XCircleIcon class="h-5 w-5 text-red-600" v-else />
                    </div>
                    <Button color="warning" data-bs-toggle="modal" data-bs-target="#riwayat">Riwayat Peminjam</Button>
                </div>

                <ul class="border shadow-md">
                    <List kolom="Judul" :text="detail.judul" />
                    <List kolom="NIK" :text="detail.nik" />
                    <List kolom="ISBN" :text="detail.isbn" />
                    <List kolom="Pengarang" :text="detail.pengarang" />
                    <List kolom="Penerbit">
                        {{ detail.penerbit ?? '-' }} |
                        {{ detail.kota_penerbit ?? '-' }} |
                        {{ detail.tahun_terbit ?? '-' }}
                    </List>
                    <List kolom="Edisi / Cetakan" :text="detail.edisi_cetakan" />
                    <List kolom="Jenis" :text="detail.jenis" />
                    <List kolom="Halaman" :text="detail.jumlah_halaman" />
                    <List kolom="Klasifikasi" :text="detail.klasifikasi" />
                    <List kolom="Lokasi" :text="detail.lokasi_penyimpanan" />
                    <List kolom="Eksemplar" :text="detail.jumlah_buku" />
                    <List kolom="Keterangan" :text="detail.keterangan" />
                    <List kolom="Tanggal" :text="detail.tanggal" />
                </ul>
            </div>
        </div>

        <!-- Modal -->
        <Modal title="Riwayat" id="riwayat" size="modal-lg">
            <Table :items="riwayat">
                <template #head>
                    <Th>Tanggal</Th>
                    <Th>Nama</Th>
                    <Th>Dari</Th>
                    <Th>Sampai</Th>
                    <Th>Status</Th>
                </template>
                <template #body="{ item }">
                    <Td :text="item.dibuat" is-time />
                    <Td :text="item.loanable.nama" />
                    <Td :text="item.dipinjam" is-date />
                    <Td :text="item.dikembalikan" is-date />
                    <Td>
                        <Badge color="success" v-if="item.returned_at">Selesai</Badge>
                        <Badge color="warning" v-else>Sedang dipinjam</Badge>
                    </Td>
                </template>
            </Table>
        </Modal>
    </Layout>

</template>