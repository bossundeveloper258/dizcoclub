import { FileModel } from "./file.model";

export interface EventModel{
    id: number;
    title: string;
    date: string;
    time: string;
    address: string;
    description: string;
    avatar_path: string;
    stock: number;
    price: string;
    isdiscount: boolean;
    discount?: number;
    discount_stock?: number;
    files: FileModel[];
}