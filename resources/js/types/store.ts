export type StoreShop = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    shop_products_count?: number;
};

export type StoreListing = {
    id: number;
    name: string | null;
    description: string | null;
    price: string;
    thumbnail_url: string | null;
    product: {
        name: string;
        description: string | null;
        sku: string;
    };
};
