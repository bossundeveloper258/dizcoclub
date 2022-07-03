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
  constructor(
    private eventService: EventService
  ) { }

  ngOnInit(): void {
    this.eventService.getAll().subscribe(
      res => {
        this.events = res;
      }
    )
  }

}
