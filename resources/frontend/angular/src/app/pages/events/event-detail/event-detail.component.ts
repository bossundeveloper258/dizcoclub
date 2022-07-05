import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NzModalService } from 'ng-zorro-antd/modal';
import { EventService } from 'src/app/core/services/event.service';
import { OrderService } from 'src/app/core/services/order.service';
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
  total: string = "";

  constructor(
    private eventService: EventService,
    private modalService: NzModalService,
    private route: Router,
    private activatedRoute: ActivatedRoute,
    private orderService: OrderService
  ) {
    this.activatedRoute.paramMap.subscribe( paramMap => {
      this.eventId = paramMap.get('id') ?? "";
    });

    
    localStorage.removeItem("q");
   }

  ngOnInit(): void {
    this.setData();
  }

  setData(): void {
    this.eventService.getById(this.eventId).subscribe(
      res => {
        this.event = res;
        this.calculateTotal();
      }
    )
  }

  addSumar(){
    this.quantity = this.quantity + 1;
    this.calculateTotal();
  }

  addRestar(){
    if( this.quantity > 1){
      this.quantity = this.quantity -1;
      this.calculateTotal();
    }
  }

  calculateTotal(){
    this.total = (this.quantity * parseFloat(this.event?.price ?? "0")).toString();
  }

  onSubmit(){
    this.orderService.postOptions({total: this.quantity}).subscribe(
      res => {
        console.log(res)
        localStorage.setItem("q", this.quantity.toString());
        this.route.navigate(['events/detail/'+this.eventId+'/guests'] , {
          queryParams: {session: res.session, purchaseNumber: res.purchaseNumber,merchantid:res.merchantid}
        });
      }
    )
    
  }


}
