interface PaginationMeta {
    path: string;
    links: Array<{
        url?: string;
        label: string;
        active: boolean;
    }>;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    from: number;
    to: number;
    total: number;
    last_page: number;
    per_page: number;
}
