import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from 'src/environments/environment';
import { StorageService } from './storage.service';
import {errorHandler} from '../../shared/utils/functions'
import { ResponseModal } from '../../shared/models/response.model';
import { Observable } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
import { EventModel } from 'src/app/shared/models/event.model';

@Injectable({
  providedIn: 'root'
})
export class EventService {

  private apiURL = environment.api + '/events/';

  private headers = new HttpHeaders()
    .set("Content-Type", `multipart/form-data`);

  constructor(
    private httpClient: HttpClient,
    private storageService: StorageService
  ) { }

  public getAll(): Observable<EventModel[]>{
    return this.httpClient.get<ResponseModal>(this.apiURL)
    .pipe(
      map( (res: ResponseModal ) => res.data ),
      catchError(errorHandler)
    );
  }

  public postCreate(body: any): Observable<any>{
    return this.httpClient.post<ResponseModal>(this.apiURL, body )
    .pipe(
      map( (res: ResponseModal ) => res.data ),
      catchError(errorHandler)
    );
  }
}
