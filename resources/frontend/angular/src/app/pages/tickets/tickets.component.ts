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
  loadingEvent: boolean = true;
  user!: UserModel;
  events: EventModel[] = [];
  selectedEvent: any;
  
  constructor(
    private orderService: OrderService,
    private storageService: StorageService,
    private eventService: EventService
  ) { 
    this.user = this.storageService.getUser();
  }

  ngOnInit(): void {
    this.getAllEvent();
  }

  onChangeTab(tab: any){
    console.log()
    this.getAllTickets(tab.index);
    
  }

  getAllTickets(event: string){
    this.loading = true;
    this.orderService.getTickets(event).subscribe(
      res => {
        this.loading = false;
        this.tickets = res;
      },
      error => {
        this.loading = false;
      }
    )
  }

  getAllEvent(){
    this.loadingEvent = true;
    this.eventService.getFindAll().subscribe(
      res => {
        this.events = res;
        this.loadingEvent = false;
        this.selectedEvent = this.events[0].id;
        if(this.user?.isadmin){
          this.getAllTickets(this.events[0].id.toString());
        }else{
          this.getAllTickets('');
        }
      },
      error => {
        this.events = [];
      }
    )
  }

  onChangeEvent(e: any){
    this.getAllTickets(e.toString());
  }

  onDownload(){
    this.orderService.getExportTicket( this.selectedEvent ).subscribe(
      response => {
        const title = this.events.find( e => e.id == this.selectedEvent )?.title;
        var _data = response;
        var _blob = new Blob([_data], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});

        var link = document.createElement('a');
        link.href = window.URL.createObjectURL(_blob);
        link.download = title+'.xlsx';
        link.click();
      }
    )
  }
}
