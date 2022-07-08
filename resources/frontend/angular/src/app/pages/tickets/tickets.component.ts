import { Component, OnInit } from '@angular/core';
import { EventService } from 'src/app/core/services/event.service';
import { OrderService } from 'src/app/core/services/order.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { UserModel } from 'src/app/shared/models/auth.model';
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
  user!: UserModel;
  
  constructor(
    private orderService: OrderService,
    private storageService: StorageService,
  ) { 
    this.user = this.storageService.getUser();
  }

  ngOnInit(): void {
    
    this.getAllTickets(0);
  }

  onChangeTab(tab: any){
    console.log()
    this.getAllTickets(tab.index);
  }

  getAllTickets(filter: number){
    this.loading = true;
    this.orderService.getTickets(filter).subscribe(
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
