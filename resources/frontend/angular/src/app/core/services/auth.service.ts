import { ErrorHandler, Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
import { environment } from 'src/environments/environment';
import { ResponseModal } from '../../shared/models/response.model';
import { AuthModel } from 'src/app/shared/models/auth.model';
import { StorageService } from './storage.service';
import {errorHandler} from '../../shared/utils/functions'

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  private apiURL = environment.api + '/auth/';

  private headers = new HttpHeaders()
    .set("Content-Type", `application/json`)
    .set("X-Requested-With", "XMLHttpRequest");

  constructor(
    private httpClient: HttpClient,
    private storageService: StorageService
  ) { }

  public login(user: string , pass: string): Observable<AuthModel>{
    return this.httpClient.post<ResponseModal>(this.apiURL + 'login', JSON.stringify({email:user, password:pass}) , {headers: this.headers})
    .pipe(
      map( (res: ResponseModal ) => res.data ),
      catchError(errorHandler)
    );
  }

  public logout(): Observable<any>{
    return this.httpClient.get<ResponseModal>(this.apiURL + 'logout' , {headers: this.headers})
    .pipe(
      map( (res: ResponseModal ) => res.data ),
      catchError(errorHandler)
    );
  }

  public resgiter(body:any): Observable<AuthModel>{
    return this.httpClient.post<ResponseModal>(this.apiURL + 'signup', body , {headers: this.headers})
    .pipe(
      map( (res: ResponseModal ) => res.data ),
      catchError(errorHandler)
    );
  }

  public isAuthenticated(): boolean {
    // get the token
    const token = this.storageService.getToken();
    // return a boolean reflecting 
    // whether or not the token is expired
    return !(token === "");
  }

  
}
