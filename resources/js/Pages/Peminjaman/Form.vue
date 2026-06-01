<script setup>
import { Layout } from '@/Layouts'
import { Button, List, Badge } from "@/Components";
import { useToast } from "@/Composables";
import { Datalist, Input, Textarea } from "@/Components/Form";
import { useForm } from '@inertiajs/inertia-vue3';

const props = defineProps({
    listBuku: {
        type: Array,
        default: () => []
    },
    nikBuku: String,
    buku: {
        type: Object,
        default: () => ({})
    },

    listUser: {
        type: Array,
        default: () => []
    },
})

const formBuku = useForm({
    nikBuku: props.nikBuku,
})

const formPinjam = useForm({
    user: null,
    buku: props.buku?.id,
    dari: null,
    sampai: null,
    catatan: null
})

function cariBuku() {
    formBuku.get(location.href)
}

function pinjam() {
    formPinjam.post(route('peminjaman.store'), {
        onSuccess: () => useToast(),
    })
}

</script>

<template>
    <Layout judul="Form Peminjaman">
        <form class="flex space-x-4" @submit.prevent="cariBuku">
            <Datalist v-model="formBuku.nikBuku" placeholder="cari nik / judul / scan barcode">
                <option :value="buku.nik" v-for="buku in listBuku">{{ buku.judul }}</option>
            </Datalist>
            <Button type="submit">Cari</Button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-6 border gap-4 mt-4 p-2" v-if="buku">
            <div class="md:col-span-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="md:col-span-2 border p-2">
                        <img :src="`/storage/sampul/${buku.sampul ?? 'contoh.png'}`" alt="sampul buku">
                    </div>
                    <div class="md:col-span-3">
                        <ul>
                            <List kolom="Judul" :text="buku.judul" />
                            <List kolom="Pengarang" :text="buku.pengarang" />
                            <List kolom="Penerbit" :text="buku.penerbit" />
                        </ul>
                        <hr class="my-4">
                        <ul>
                            <List kolom="Status">
                                <Badge color="success" v-if="buku.jumlah_buku > buku.dipinjam">Tersedia</Badge>
                                <Badge color="danger" v-else>Tidak Tersedia</Badge>
                            </List>
                            <List kolom="Dipinjam" :text="buku.dipinjam + ' / ' + buku.jumlah_buku" />
                        </ul>
                    </div>
                </div>
            </div>
            <div class="md:col-span-2" v-if="buku.jumlah_buku > buku.dipinjam">
                <form @submit.prevent="pinjam">
                    <div class="border p-2 space-y-4">
                        <Datalist v-model="formPinjam.user" label="Peminjam" placeholder="cari nama / nik / nis"
                            :invalid="formPinjam.errors.user" required>
                            <option :value="user.nis ?? user.nik" v-for="user in listUser">{{ user.nama }}</option>
                        </Datalist>
                        <Input v-model="formPinjam.dari" label="Dari" type="date" :invalid="formPinjam.errors.dari" required />
                        <Input v-model="formPinjam.sampai" label="Sampai" type="date" :min="formPinjam.dari"
                            :invalid="formPinjam.errors.sampai" required />
                        <Textarea v-model="formPinjam.catatan" label="Catatan" placeholder="Tulis Catatan"
                            :invalid="formPinjam.errors.catatan"></Textarea>
                        <div class="text-red-500" v-if="formPinjam.errors.buku">{{ formPinjam.errors.buku }}</div>

                        <Button type="submit" color="success" :disabled="formPinjam.processing">Pinjam</Button>
                    </div>
                </form>

            </div>
        </div>
    </Layout>
</template>