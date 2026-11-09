export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
    is_admin: boolean;
    is_shop_owner: boolean;
    permissions: string[];
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
