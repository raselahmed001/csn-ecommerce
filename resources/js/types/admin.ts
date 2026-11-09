export type ShopOwner = {
    id: number;
    name: string;
    email: string;
    shops_count?: number;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
};

export type OwnerOption = {
    id: number;
    name: string;
    email: string;
};

export type Shop = {
    id: number;
    owner_id: number;
    name: string;
    slug: string;
    description: string | null;
    owner?: OwnerOption;
};

export type Product = {
    id: number;
    name: string;
    slug: string;
    sku: string;
    description: string | null;
};
