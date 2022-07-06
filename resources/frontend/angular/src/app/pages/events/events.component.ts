import { Component, OnInit } from '@angular/core';
import { EventService } from 'src/app/core/services/event.service';
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
  constructor(
    private eventService: EventService
  ) { }

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

}
