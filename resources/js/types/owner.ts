export type OwnerShop = {
    id: number;
    name: string;
    slug: string;
    shop_products_count?: number;
};

export type CatalogProduct = {
    id: number;
    name: string;
    slug: string;
    sku: string;
    description: string | null;
};

export type ShopListing = {
    id: number;
    shop_id: number;
    product_id: number;
    name: string | null;
    description: string | null;
    price: string;
    thumbnail: string | null;
    thumbnail_url: string | null;
    product?: CatalogProduct;
};
