import { Injectable } from '@angular/core';
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor
} from '@angular/common/http';

import { Observable } from 'rxjs';
import { AuthService } from '../services/auth.service';
import { StorageService } from '../services/storage.service';

@Injectable()
export class TokenInterceptor implements HttpInterceptor {
  constructor(
    public auth: AuthService,
    private storageService: StorageService
  ) {}
  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {

    
    if( this.auth.isAuthenticated() ){
        request = request.clone({
            setHeaders: {
              Authorization: `Bearer ${this.storageService.getToken()}`
            }
        });
    }
    
    return next.handle(request);
  }
}