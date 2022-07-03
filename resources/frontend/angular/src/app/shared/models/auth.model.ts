export interface AuthModel{
    token: string;
    name: string;
    user: UserModel;
}

export interface UserModel{
    isadmin: boolean; 
    email: string;
    name: string;
}