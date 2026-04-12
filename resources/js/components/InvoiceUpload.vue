<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Props = {
    uploadUrl: string;
    cycleMesReferencia: string;
};

const props = defineProps<Props>();

const dragOver = ref(false);
const fileError = ref<string | null>(null);
const selectedFile = ref<File | null>(null);

const form = useForm({
    arquivo: null as File | null,
    numero_nota: '',
    valor: '',
    data_emissao: '',
    cnpj_emissor: '',
    cnpj_destinatario: '',
});

function formatFileSize(bytes: number): string {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function validateFile(file: File): string | null {
    if (file.type !== 'application/pdf') {
        return 'Apenas arquivos PDF são aceitos.';
    }

    if (file.size > 10 * 1024 * 1024) {
        return 'O arquivo não pode exceder 10 MB.';
    }

    return null;
}

function handleFileSelect(file: File) {
    const error = validateFile(file);

    if (error) {
        fileError.value = error;
        selectedFile.value = null;
        form.arquivo = null;

        return;
    }

    fileError.value = null;
    selectedFile.value = file;
    form.arquivo = file;
}

function onFileInputChange(event: Event) {
    const input = event.target as HTMLInputElement;

    if (input.files && input.files[0]) {
        handleFileSelect(input.files[0]);
    }
}

function onDrop(event: DragEvent) {
    dragOver.value = false;
    const file = event.dataTransfer?.files[0];

    if (file) {
        handleFileSelect(file);
    }
}

function onDragOver(event: DragEvent) {
    event.preventDefault();
    dragOver.value = true;
}

function onDragLeave() {
    dragOver.value = false;
}

function submit() {
    form.post(props.uploadUrl, {
        forceFormData: true,
    });
}
</script>

<template>
    <div class="flex flex-col gap-4">
        <h2 class="text-lg font-semibold">
            Enviar Nota Fiscal — {{ cycleMesReferencia }}
        </h2>

        <form class="flex flex-col gap-4" @submit.prevent="submit">
            <!-- Drag-and-drop area -->
            <div
                class="cursor-pointer rounded-lg border-2 border-dashed p-6 text-center transition-colors"
                :class="
                    dragOver
                        ? 'border-primary bg-primary/5'
                        : 'border-muted-foreground/30 hover:border-primary/50'
                "
                @dragover="onDragOver"
                @dragleave="onDragLeave"
                @drop.prevent="onDrop"
                @click="($refs.fileInput as HTMLInputElement).click()"
            >
                <input
                    ref="fileInput"
                    type="file"
                    accept="application/pdf"
                    class="hidden"
                    @change="onFileInputChange"
                />
                <div v-if="selectedFile">
                    <p class="font-medium text-foreground">
                        {{ selectedFile.name }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ formatFileSize(selectedFile.size) }}
                    </p>
                </div>
                <div v-else>
                    <p class="text-muted-foreground">
                        Arraste um PDF aqui ou clique para selecionar
                    </p>
                    <p class="text-xs text-muted-foreground/70">
                        PDF apenas · máx. 10 MB
                    </p>
                </div>
            </div>

            <p v-if="fileError" class="text-sm text-destructive">
                {{ fileError }}
            </p>
            <p v-if="form.errors.arquivo" class="text-sm text-destructive">
                {{ form.errors.arquivo }}
            </p>

            <!-- Form fields -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="flex flex-col gap-1.5">
                    <Label for="numero_nota">Número da Nota</Label>
                    <Input
                        id="numero_nota"
                        v-model="form.numero_nota"
                        placeholder="NF-001"
                        maxlength="50"
                    />
                    <p
                        v-if="form.errors.numero_nota"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.numero_nota }}
                    </p>
                </div>

                <div class="flex flex-col gap-1.5">
                    <Label for="valor">Valor (R$)</Label>
                    <Input
                        id="valor"
                        v-model="form.valor"
                        type="number"
                        step="0.01"
                        min="0.01"
                        placeholder="0,00"
                    />
                    <p
                        v-if="form.errors.valor"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.valor }}
                    </p>
                </div>

                <div class="flex flex-col gap-1.5">
                    <Label for="data_emissao">Data de Emissão</Label>
                    <Input
                        id="data_emissao"
                        v-model="form.data_emissao"
                        type="date"
                    />
                    <p
                        v-if="form.errors.data_emissao"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.data_emissao }}
                    </p>
                </div>

                <div class="flex flex-col gap-1.5">
                    <Label for="cnpj_emissor">CNPJ Emissor</Label>
                    <Input
                        id="cnpj_emissor"
                        v-model="form.cnpj_emissor"
                        placeholder="00.000.000/0000-00"
                        maxlength="18"
                    />
                    <p
                        v-if="form.errors.cnpj_emissor"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.cnpj_emissor }}
                    </p>
                </div>

                <div class="flex flex-col gap-1.5">
                    <Label for="cnpj_destinatario">CNPJ Destinatário</Label>
                    <Input
                        id="cnpj_destinatario"
                        v-model="form.cnpj_destinatario"
                        placeholder="00.000.000/0000-00"
                        maxlength="18"
                    />
                    <p
                        v-if="form.errors.cnpj_destinatario"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.cnpj_destinatario }}
                    </p>
                </div>
            </div>

            <Button
                type="submit"
                :disabled="form.processing || !selectedFile"
                class="self-start"
            >
                {{ form.processing ? 'Enviando...' : 'Enviar Nota Fiscal' }}
            </Button>
        </form>
    </div>
</template>
