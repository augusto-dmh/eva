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
        title: 'Bem-vindo de volta',
        description: 'Acesse com seu e-mail corporativo',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Entrar" />

    <div
        v-if="status"
        class="mb-4 rounded-lg border border-blue-500/20 bg-blue-50 p-3 text-center text-sm text-blue-700"
    >
        {{ status }}
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5"
    >
        <div class="grid gap-4">
            <div class="grid gap-1.5">
                <Label
                    for="email"
                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                >
                    E-mail
                </Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="nome@clubedovalor.com.br"
                    class="h-11"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-1.5">
                <div class="flex items-center justify-between">
                    <Label
                        for="password"
                        class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                    >
                        Senha
                    </Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-xs text-blue-600 hover:text-blue-800"
                        :tabindex="5"
                    >
                        Esqueceu?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="h-11"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center gap-2">
                <Checkbox id="remember" name="remember" :tabindex="3" />
                <Label for="remember" class="text-xs text-muted-foreground"
                    >Lembrar por 30 dias</Label
                >
            </div>

            <Button
                type="submit"
                class="h-11 w-full bg-blue-600 font-semibold tracking-wide text-white hover:bg-blue-500 active:bg-blue-700"
                style="font-family: 'Syne', sans-serif; letter-spacing: 0.05em"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" class="mr-2 size-4" />
                {{ processing ? 'Entrando…' : 'Entrar' }}
            </Button>
        </div>
    </Form>
</template>
