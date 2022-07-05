import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NzModalService } from 'ng-zorro-antd/modal';
import { EventService } from 'src/app/core/services/event.service';
import { EventModel } from 'src/app/shared/models/event.model';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-event-detail',
  templateUrl: './event-detail.component.html',
  styleUrls: ['./event-detail.component.css']
})
export class EventDetailComponent implements OnInit {

  eventId: string = '';
  routeStorage = environment.storageUrl;
  event?: EventModel;
  quantity: number = 1;
  
  constructor(
    private eventService: EventService,
    private modalService: NzModalService,
    private route: Router,
    private activatedRoute: ActivatedRoute
  ) {
    this.activatedRoute.paramMap.subscribe( paramMap => {
      this.eventId = paramMap.get('id') ?? "";
    })
   }

  ngOnInit(): void {
    this.setData();
  }

  setData(): void {
    this.eventService.getById(this.eventId).subscribe(
      res => {
        this.event = res;
      }
    )
  }



}
