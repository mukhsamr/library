<script setup>
import { Layout } from '@/Layouts';
import { Input, FileInput, Datalist } from '@/Components/Form';
import { Button, Modal } from '@/Components';
import { useToast, useList } from '@/Composables';
import { useForm } from '@inertiajs/inertia-vue3';
import { ref } from 'vue';
import { PhotoIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { BookmarkIcon } from '@heroicons/vue/24/solid';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
    listBuku: {
        type: Array,
        default: () => []
    },
    nikBuku: String,
    buku: {
        type: Object,
        default: () => ({})
    }

})

const formEdit = useForm({
    _method: 'patch',
    id: props.buku?.id,
    nik: props.buku?.nik,
    judul: props.buku?.judul,
    tanggal: props.buku?.tanggal,
    pengarang: props.buku?.pengarang,
    kota_penerbit: props.buku?.kota_penerbit,
    penerbit: props.buku?.penerbit,
    edisi_cetakan: props.buku?.edisi_cetakan,
    tahun_terbit: props.buku?.tahun_terbit,
    isbn: props.buku?.isbn,
    sumber: props.buku?.sumber,
    klasifikasi: props.buku?.klasifikasi,
    lokasi_penyimpanan: props.buku?.lokasi_penyimpanan,
    jenis: props.buku?.jenis,
    jumlah_halaman: props.buku?.jumlah_halaman,
    jumlah_buku: props.buku?.jumlah_buku,
    deskripsi: props.buku?.deskripsi,
    sampul: null
})

const urlPreview = ref(props.buku?.sampul ? '/storage/sampul/' + props.buku.sampul : null)
const fotoSize = ref()
const nikBuku = ref(props.nikBuku)

function cari() {
    Inertia.get(route('form.edit', { nik: nikBuku.value }))
}

function previewGambar(e) {
    const file = e.target.files[0]

    urlPreview.value = URL.createObjectURL(file)
    fotoSize.value = Math.round(file.size / 1000) + ' KB'
}

function simpan() {
    formEdit.post(route('form.update'), {
        onSuccess: () => {
            useToast()
            formEdit.reset()
        },
        onError: (errors) => {
            useToast({
                icon: 'warning',
                title: useList(errors)
            })
        }
    })
}


</script>

<template>

    <Layout judul="Edit Buku">
        <form class="flex space-x-2" @submit.prevent="cari">
            <Datalist v-model="nikBuku" placeholder="cari nik / judul / scan barcode">
                <option v-for="buku in listBuku" :value="buku.nik">{{ buku.judul }}</option>
            </Datalist>
            <Button type="submit">Cari</Button>
        </form>

        <form @submit.prevent="simpan" enctype="multipart/form-data">
            <div class="my-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-3 border">
                    <Input v-model="formEdit.nik" label="NIK" :invalid="formEdit.errors.nik" required />
                    <Input v-model="formEdit.judul" label="Judul" :invalid="formEdit.errors.judul" required />
                    <Input v-model="formEdit.tanggal" label="Tanggal" :invalid="formEdit.errors.tanggal" type="date" />
                    <Input v-model="formEdit.pengarang" label="Pengarang" :invalid="formEdit.errors.pengarang" />
                    <Input v-model="formEdit.kota_penerbit" label="Kota Penerbit"
                        :invalid="formEdit.errors.kota_penerbit" />
                    <Input v-model="formEdit.penerbit" label="Penerbit" :invalid="formEdit.errors.penerbit" />
                    <Input v-model="formEdit.edisi_cetakan" label="Edisi / Cetakan"
                        :invalid="formEdit.errors.edisi_cetakan" />
                    <Input v-model="formEdit.tahun_terbit" label="Tahun Terbit" :invalid="formEdit.errors.tahun_terbit"
                        type="number" />
                    <Input v-model="formEdit.isbn" label="ISBN" :invalid="formEdit.errors.isbn" />
                    <Input v-model="formEdit.sumber" label="Sumber" :invalid="formEdit.errors.sumber" />
                    <Input v-model="formEdit.klasifikasi" label="Klasifikasi" :invalid="formEdit.errors.klasifikasi" />
                    <Input v-model="formEdit.lokasi_penyimpanan" label="Lokasi Penyimpanan"
                        :invalid="formEdit.errors.lokasi_penyimpanan" />
                    <Input v-model="formEdit.jenis" :label="true" :invalid="formEdit.errors.jenis"
                        placeholder="Pisahkan dengan strip (Dewasa - Islam) ">
                    <div class="flex items-center">
                        <span>Jenis</span>
                        <div class="relative">
                            <BookmarkIcon class="h-4 w-4 ml-2 text-yellow-500 clickable peer" />
                            <div
                                class="absolute border shadow-lg rounded-md bg-white py-2 px-4 peer-active:block hidden">
                                <ul class="text-sm group-active:block">
                                    <li class="whitespace-nowrap">Anak</li>
                                    <li class="whitespace-nowrap">Anak Besar</li>
                                    <li class="whitespace-nowrap">Arab</li>
                                    <li class="whitespace-nowrap">Dewasa</li>
                                    <li class="whitespace-nowrap">English</li>
                                    <li class="whitespace-nowrap">Saku</li>
                                    <li class="whitespace-nowrap">Referensi</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    </Input>

                    <Input v-model="formEdit.jumlah_halaman" label="Jumlah Halaman"
                        :invalid="formEdit.errors.jumlah_halaman" type="number" min="1" step="1" />
                    <Input v-model="formEdit.jumlah_buku" label="Eksemplar" :invalid="formEdit.errors.jumlah_buku"
                        type="number" min="1" step="1" />
                    <Input v-model="formEdit.deskripsi" label="Deskripsi" :invalid="formEdit.errors.deskripsi" />
                </div>
            </div>

            <div class="flex justify-between">
                <div class="flex space-x-2">
                    <Button color="warning" data-bs-toggle="modal" data-bs-target="#sampul" :icon="PhotoIcon">
                        Pilih Sampul
                    </Button>
                    <Button color="success" type="submit"
                        :disabled="formEdit.processing || !formEdit.isDirty">Perbaharui</Button>
                </div>
                <Button color="danger" class="float-right hidden" :icon="TrashIcon">Hapus</Button>
            </div>
        </form>



        <!-- Modal -->
        <Modal title="Pilih Sampul" id="sampul">
            <FileInput v-model="formEdit.sampul" accept=".png,.jpg,.jpeg,.webp" :invalid="formEdit.errors.sampul"
                @change="previewGambar($event)" />
            <div class="text-sm text-red-500 mt-1">Gambar tidak boleh lebih dari 2048 KB</div>
            <div class="p-4 border mt-2" v-if="urlPreview">
                <div class="text-sm mb-1">Ukuran file: <b>{{ fotoSize }}</b></div>
                <img :src="urlPreview" alt="preview">
            </div>
            <template #footer>
                <Button data-bs-dismiss="modal">OK</Button>
            </template>
        </Modal>
    </Layout>
</template>