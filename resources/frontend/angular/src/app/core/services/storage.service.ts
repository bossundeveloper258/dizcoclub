import { Injectable } from '@angular/core';
import { UserModel } from 'src/app/shared/models/auth.model';

@Injectable({
  providedIn: 'root'
})
export class StorageService {

  constructor() { }

  public setToken( token:  string ){
    localStorage.setItem('token' , token);
  }

  public getToken(): string {
    return localStorage.getItem("token") ?? "";
  }

  public setUser(user: UserModel){
    localStorage.setItem('user' , JSON.stringify(user));
  }

  public getUser(): any{
    return localStorage.getItem("user") ? JSON.parse(localStorage.getItem("user")??"") : false;
  }

  public removeStorage(): void {
    localStorage.clear();
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    // localStorage.removeItem('token');
  }
}
