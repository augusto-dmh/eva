export type UserRole = 'admin' | 'collaborator';

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    role: UserRole;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
