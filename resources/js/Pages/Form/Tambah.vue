<script setup>
import { Layout } from '@/Layouts';
import { Input, FileInput } from '@/Components/Form';
import { Button, Modal } from '@/Components';
import { useToast, useList } from '@/Composables';
import { useForm } from '@inertiajs/inertia-vue3';
import { ref } from 'vue';
import { PhotoIcon } from '@heroicons/vue/24/outline';
import { BookmarkIcon } from '@heroicons/vue/24/solid';

const formTambah = useForm({
    nik: null,
    judul: null,
    tanggal: null,
    pengarang: null,
    kota_penerbit: null,
    penerbit: null,
    edisi_cetakan: null,
    tahun_terbit: null,
    isbn: null,
    sumber: null,
    klasifikasi: null,
    lokasi_penyimpanan: null,
    jenis: null,
    jumlah_halaman: null,
    jumlah_buku: null,
    deskripsi: null,
    sampul: null
})

const urlPreview = ref()
const fotoSize = ref()

function previewGambar(e) {
    const file = e.target.files[0]

    urlPreview.value = URL.createObjectURL(file)
    fotoSize.value = Math.round(file.size / 1000) + ' KB'
}

function simpan() {
    formTambah.post(route('form.store'), {
        onSuccess: () => {
            useToast()
            formTambah.reset()
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

    <Layout judul="Tambah Buku">
        <form @submit.prevent="simpan" enctype="multipart/form-data">
            <div class="my-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-3 border">
                    <Input v-model="formTambah.nik" label="NIK" :invalid="formTambah.errors.nik" required />
                    <Input v-model="formTambah.judul" label="Judul" :invalid="formTambah.errors.judul" required />
                    <Input v-model="formTambah.tanggal" label="Tanggal" :invalid="formTambah.errors.tanggal"
                        type="date" />
                    <Input v-model="formTambah.pengarang" label="Pengarang" :invalid="formTambah.errors.pengarang" />
                    <Input v-model="formTambah.kota_penerbit" label="Kota Penerbit"
                        :invalid="formTambah.errors.kota_penerbit" />
                    <Input v-model="formTambah.penerbit" label="Penerbit" :invalid="formTambah.errors.penerbit" />
                    <Input v-model="formTambah.edisi_cetakan" label="Edisi / Cetakan"
                        :invalid="formTambah.errors.edisi_cetakan" />
                    <Input v-model="formTambah.tahun_terbit" label="Tahun Terbit"
                        :invalid="formTambah.errors.tahun_terbit" type="number" />
                    <Input v-model="formTambah.isbn" label="ISBN" :invalid="formTambah.errors.isbn" />
                    <Input v-model="formTambah.sumber" label="Sumber" :invalid="formTambah.errors.sumber" />
                    <Input v-model="formTambah.klasifikasi" label="Klasifikasi"
                        :invalid="formTambah.errors.klasifikasi" />
                    <Input v-model="formTambah.lokasi_penyimpanan" label="Lokasi Penyimpanan"
                        :invalid="formTambah.errors.lokasi_penyimpanan" />
                    <Input v-model="formTambah.jenis" :label="true" :invalid="formTambah.errors.jenis"
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

                    <Input v-model="formTambah.jumlah_halaman" label="Jumlah Halaman"
                        :invalid="formTambah.errors.jumlah_halaman" type="number" min="1" step="1" />
                    <Input v-model="formTambah.jumlah_buku" label="Eksemplar" :invalid="formTambah.errors.jumlah_buku"
                        type="number" min="1" step="1" />
                    <Input v-model="formTambah.deskripsi" label="Deskripsi" :invalid="formTambah.errors.deskripsi" />
                </div>
            </div>

            <div class="flex space-x-2">
                <Button color="warning" data-bs-toggle="modal" data-bs-target="#sampul" :icon="PhotoIcon">
                    Pilih Sampul
                </Button>
                <Button color="success" type="submit"
                    :disabled="formTambah.processing || !formTambah.isDirty">Simpan</Button>
            </div>
        </form>



        <!-- Modal -->
        <Modal title="Pilih Sampul" id="sampul">
            <FileInput v-model="formTambah.sampul" accept=".png,.jpg,.jpeg,.webp" :invalid="formTambah.errors.sampul"
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