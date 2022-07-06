export interface AuthModel{
    token: string;
    name: string;
    user: UserModel;
}

export interface UserModel{
    id: number;
    isadmin: boolean; 
    email: string;
    name: string;
    phone: string;
    dni: string;
}