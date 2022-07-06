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
  loading: boolean = true;
  eventId: string = '';
  routeStorage = environment.storageUrl;
  event!: EventModel;
  quantity: number = 1;
  total: string = "";
  stockTotal: number = 0;
  loadingBtn: boolean = false;

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

   }

  ngOnInit(): void {
    this.setData();
  }

  setData(): void {
    this.loading = true;
    this.eventService.getById(this.eventId).subscribe(
      res => {
        this.event = res;
        this.calculateTotal();
        this.loading = false;
        let _totalSctock = this.event.orders.reduce((c, obj) => { return c + obj.q } ,0);
        this.stockTotal = this.event.stock - _totalSctock;
      },
      error => {
        this.loading = false;
      }
    )
  }

  addSumar(){
    if( this.stockTotal != 0){
      this.quantity = this.quantity + 1;
      this.calculateTotal();
      this.stockTotal -= 1;
    }
  }

  addRestar(){
    if( this.quantity > 1){
      this.quantity = this.quantity -1;
      this.calculateTotal();
      this.stockTotal += 1;
    }
  }

  calculateTotal(){
    if( this.event?.isdiscount){
      this.total = (this.quantity * parseFloat(this.event?.price ?? "0") *( 1 - ( (this.event?.discount ?? 100) / 100) ) ).toString();
    }else{
      this.total = (this.quantity * parseFloat(this.event?.price ?? "0")).toString();
    }

    this.total = parseFloat(this.total).toFixed(2);
    
  }

  onSubmit(){
    this.loadingBtn = true;
    this.orderService.postOptions({total: this.quantity, event_id: this.eventId}).subscribe(
      res => {
        this.route.navigate(['events/detail/'+this.eventId+'/guests'] , {
          queryParams: {
            s: res.session, 
            p: res.purchaseNumber,
            m: res.merchantid,
            e: this.eventId,
            q: this.quantity,
            t: res.totalAmount
          }
        });
        this.loadingBtn = false;
      },
      error =>{
        this.loadingBtn = false;
        this.modalService.info({
          nzTitle: "Info",
          nzContent: error.message
        });
      }
    )
    
  }


}
