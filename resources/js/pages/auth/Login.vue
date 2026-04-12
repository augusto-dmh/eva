<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Acesse sua conta',
        description: 'Entre com seu e-mail corporativo',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Entrar" />

    <div v-if="status" class="mb-4 text-center text-sm font-medium text-emerald-400">
        {{ status }}
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5"
    >
        <div class="grid gap-5">
            <div class="grid gap-2">
                <Label for="email" class="text-sm font-medium text-slate-300">E-mail corporativo</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="nome@clubedovalor.com.br"
                    class="border-slate-700 bg-slate-800/50 text-white placeholder:text-slate-500 focus:border-blue-500"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password" class="text-sm font-medium text-slate-300">Senha</Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-xs text-blue-400 hover:text-blue-300"
                        :tabindex="5"
                    >
                        Esqueceu a senha?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="border-slate-700 bg-slate-800/50 text-white placeholder:text-slate-500 focus:border-blue-500"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center gap-2">
                <Checkbox id="remember" name="remember" :tabindex="3" />
                <Label for="remember" class="text-sm text-slate-400">Lembrar por 30 dias</Label>
            </div>

            <Button
                type="submit"
                class="mt-2 w-full bg-blue-600 font-semibold text-white hover:bg-blue-500"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" class="mr-2" />
                Entrar com segurança
            </Button>
        </div>
    </Form>
</template>
