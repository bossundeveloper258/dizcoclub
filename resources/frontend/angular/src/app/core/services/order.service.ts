import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from 'src/environments/environment';
import { StorageService } from './storage.service';
import {errorHandler} from '../../shared/utils/functions'
import { ResponseModal } from '../../shared/models/response.model';
import { Observable } from 'rxjs';
import { catchError, map } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class OrderService {
  private apiURL = environment.api + '/orders';
  constructor(
    private httpClient: HttpClient,
    private storageService: StorageService
  ) { }

  public postOptions(body: any): Observable<any>{
    return this.httpClient.post<ResponseModal>(this.apiURL+"/options", body)
    .pipe(
      map( (res: ResponseModal ) => res.data ),
      catchError(errorHandler)
    );
  }

  public postCreate(body: any): Observable<any>{
    return this.httpClient.post<ResponseModal>(this.apiURL+"/create", body)
    .pipe(
      map( (res: ResponseModal ) => res.data ),
      catchError(errorHandler)
    );
  }

  public getTickets(filter: number): Observable<any>{
    return this.httpClient.get<ResponseModal>(this.apiURL+"/tickets?filter="+filter)
    .pipe(
      map( (res: ResponseModal ) => res.data ),
      catchError(errorHandler)
    );
  }

  public getTicketByToken(token: string): Observable<any>{
    return this.httpClient.get<ResponseModal>(this.apiURL+"/tickets/"+token)
    .pipe(
      map( (res: ResponseModal ) => res.data ),
      catchError(errorHandler)
    );
  }

  public postConfirmAssist(token: any): Observable<any>{
    return this.httpClient.post<ResponseModal>(this.apiURL+"/tickets/assist", {id: token})
    .pipe(
      map( (res: ResponseModal ) => res.data ),
      catchError(errorHandler)
    );
  }

}
