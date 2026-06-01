<script setup>
import { Layout } from '@/Layouts'
import { Button } from '@/Components'
import { Input, Switch } from '@/Components/Form'
import { CameraIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { useForm } from '@inertiajs/inertia-vue3';
import { ref } from 'vue';
import { useToast } from '@/Composables';

const props = defineProps({
    user: {
        type: Object,
        default: () => ({})
    }
})
const showPass = ref(false)
const previewFoto = ref()

const formUser = useForm({
    _method: 'patch',
    id: props.user.id,
    nama: props.user.nama,
    password: null,
    confirm: null,
    foto: null
})


function preview(el) {
    previewFoto.value = URL.createObjectURL(el.files[0])
}

function removePreview() {
    previewFoto.value = null
    formUser.foto = null
}

function simpan() {
    formUser.post(route('user.update'), {
        onBefore: () => {
            if (formUser.password) {
                const verif = formUser.password === formUser.confirm

                if (!verif) alert('Password tidak sama')
                return verif
            }

        },
        onSuccess: () => useToast()
    })
}

</script>

<template>
    <Layout judul="Setelan Profil">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mt-4 px-8">
            <div class="col-span-2">
                <div class="relative w-32 h-32 mx-auto">
                    <template v-if="previewFoto">
                        <div class="rounded-full w-full h-full overflow-hidden">
                            <img :src="previewFoto" draggable="false"
                                class="w-full h-full object-cover object-center select-none" alt="profil preview">
                        </div>
                        <div
                            class="rounded-full bg-slate-600 border-2 border-slate-200 h-10 w-10 p-2 absolute right-0 bottom-1 clickable">
                            <XMarkIcon class="text-white" @click="removePreview" />
                        </div>
                    </template>
                    <template v-else>
                        <div class="rounded-full w-full h-full overflow-hidden">
                            <img :src="'/storage/images/' + (user.foto ? user.foto : 'user.jpg')" draggable="false"
                                class="w-full h-full object-cover object-center select-none" alt="profil">
                        </div>
                        <input type="file" accept="image/png,.jpg,.jpeg" @input="formUser.foto = $event.target.files[0]"
                            @change="preview($event.target)" class="hidden" id="profil">
                        <label for="profil">
                            <div
                                class="rounded-full bg-green-600 border-2 border-slate-200 h-10 w-10 p-2 absolute right-0 bottom-1 clickable">
                                <CameraIcon class="text-white" />
                            </div>
                        </label>
                    </template>
                </div>
            </div>
            <div class="col-span-4">
                <form @submit.prevent="simpan">
                    <div class="space-y-2">
                        <Input v-model="formUser.nama" placeholder="username" :invalid="formUser.errors.nama" required />
                        <div class="text-red-500" v-if="formUser.errors.foto">{{ formUser.errors.foto }}</div>
                        <Input v-model="formUser.password" placeholder="password ( kosongkan jika tidak diubah )"
                            :type="showPass ? 'text' : 'password'" :invalid="formUser.errors.password" />
                        <Input v-model="formUser.confirm" placeholder="konfirmasi password"
                            :type="showPass ? 'text' : 'password'" v-if="formUser.password" />

                        <span class="text-red-500" v-if="(formUser.password !== formUser.confirm) && formUser.password">
                            Password tidak sama
                        </span>
                    </div>
                    <Switch v-model="showPass" class="mt-2">Tampilkan Password</Switch>
                    <Button type="submit" class="mt-4" :disabled="!formUser.isDirty">Simpan</Button>
                </form>
            </div>
        </div>
    </Layout>
</template>