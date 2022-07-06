import { Component, OnInit } from '@angular/core';
import { EventService } from 'src/app/core/services/event.service';
import { OrderService } from 'src/app/core/services/order.service';
import { EventModel } from 'src/app/shared/models/event.model';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-tickets',
  templateUrl: './tickets.component.html',
  styleUrls: ['./tickets.component.css']
})
export class TicketsComponent implements OnInit {

  tickets: any[] = [];
  routeStorage = environment.storageUrl;
  loading: boolean = true;
  constructor(
    private orderService: OrderService
  ) { }

  ngOnInit(): void {
    this.loading = true;
    this.orderService.getTickets().subscribe(
      res => {
        this.loading = false;
        this.tickets = res;
      },
      error => {
        this.loading = false;
      }
    )
  }

}
