import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { EventService } from 'src/app/core/services/event.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { UserModel } from 'src/app/shared/models/auth.model';
import { EventModel } from 'src/app/shared/models/event.model';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-events',
  templateUrl: './events.component.html',
  styleUrls: ['./events.component.css']
})
export class EventsComponent implements OnInit {

  events: EventModel[] = [];
  routeStorage = environment.storageUrl;
  loading: boolean = true;
  user!: UserModel;

  constructor(
    private eventService: EventService,
    private storageService: StorageService,
    private router: Router
  ) {
    this.user = this.storageService.getUser();
  }

  ngOnInit(): void {
    this.loading = true;
    this.eventService.getAll().subscribe(
      res => {
        this.loading = false;
        this.events = res;
      },
      error => {
        this.loading = false;
      }
    )
  }

  goTo( eventId: any ): void {
    if( this.user?.isadmin ){
      this.router.navigate(['events/detail', eventId , 'form']);
    }else{
      this.router.navigate(['events/detail'], eventId);
    }
  }

}
