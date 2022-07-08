import { Component, OnInit, Renderer2 } from '@angular/core';
import { FormArray, FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { NzModalService } from 'ng-zorro-antd/modal';
import { OrderService } from 'src/app/core/services/order.service';
import { ScriptService } from 'src/app/core/services/script.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { UserModel } from 'src/app/shared/models/auth.model';
import { environment } from 'src/environments/environment';


declare var VisanetCheckout: any;

@Component({
  selector: 'app-envent-guests',
  templateUrl: './envent-guests.component.html',
  styleUrls: ['./envent-guests.component.css']
})
export class EnventGuestsComponent implements OnInit {

  validateForm!: FormGroup;
  guestsArray!: FormArray;
  user!: UserModel;
  quantity!: number;
  session!: string;
  purchaseNumber!: string;
  merchantid!: string;
  total!: number;
  urlHost= environment.urlHost;
  eventId!: number;

  constructor(
    private fb: FormBuilder,
    private storageService: StorageService,
    private orderService: OrderService,
    private activatedRoute: ActivatedRoute,
    private modalService: NzModalService,
    private renderer: Renderer2,
    private scriptService: ScriptService
  ) {

    this.activatedRoute.queryParams
      .subscribe(params => {

        this.session = params.s;
        this.purchaseNumber = params.p;
        this.merchantid = params.m;
        this.quantity = params.q;
        this.total = params.t
        this.eventId = params.e;
      }
    );

    this.user = this.storageService.getUser(); 

    this.validateForm = this.fb.group({
      guestList: this.fb.array([
        this.fb.group({
          name: [{value: this.user?.name ?? "" , disabled: this.user? true: false} , [Validators.required]],
          lastname: [{value: "" , disabled: this.user? true: false} ],
          email: [{value: this.user?.email ?? "" , disabled: this.user? true: false} , [Validators.required]],
          dni: [{value: this.user?.dni ?? "" , disabled: this.user? true: false} , [Validators.required, Validators.maxLength(8), Validators.pattern("^[0-9]*$")]]
        })
      ])
    });
    
    this.guestsArray = this.validateForm.get('guestList') as FormArray;

  }

  ngOnInit(): void {
    if(this.quantity > 1){
      const formGroup:FormGroup = this.fb.group({
        name: ["" , [Validators.required]],
        lastname: ["" ],
        dni: ["" , [Validators.required, Validators.maxLength(8), Validators.pattern("^[0-9]*$")]]
      });
      for (let i = 1; i <= this.quantity - 1; i++) {
        this.guestsArray.push(formGroup);
      }
      
    }
  }

  submitForm(){

    if (this.isFormValid()) {

      const _body = this.validateForm.get('guestList')?.value;

      let body = [];
      if(this.user && this.quantity > 1 ){
        let g = [
          { name: this.user?.name , lastname: "" , email: this.user?.email, dni: this.user?.dni}
        ]
        body = g.concat(_body);
      }else{
        body = _body;
      }

      this.orderService.postCreate({
        event_id : this.eventId,
        quantity : this.quantity,
        user_id : this.user?.id ?? null,
        clients: body
      }).subscribe(
        (res) => {

          const scriptElement = this.scriptService.loadJsScript(this.renderer, environment.visaJS);
          scriptElement.onload = () => {
        
            VisanetCheckout.configure({
              sessiontoken: res.session,
              channel:'web',
              merchantid: res.merchantid,
              purchasenumber: res.order,
              amount: this.total,
              expirationminutes:'20',
              timeouturl:'about:blank',
              merchantlogo:this.urlHost+'/assets/angular/assets/img/LOGO.png',
              formbuttoncolor:'#000000',
              action: this.urlHost+'/api/orders/payment?o='+res.order+'&a='+this.total,
              complete: function(params: any) {
                console.log(params)
                alert(JSON.stringify(params));
              }
            });
            VisanetCheckout.open();

          }
          scriptElement.onerror = () => {
            
          }
        },
        (error) => {
          this.modalService.info({
            nzTitle: "Info",
            nzContent: error.message
          });
        }
      );

      
    } else {
      console.log("error Validate")
      const control = this.validateForm.get('guestList') as FormArray;
      for (const i in control.controls) {
        const controlTwo = control.controls[i] as FormGroup;
        
        for (const k in controlTwo.controls) {
          
          controlTwo.controls[k].markAsDirty();
          controlTwo.controls[k].updateValueAndValidity({onlySelf:false});
        }
      }
      
    }
  
  }

  isFormValid() : boolean { 
    return this.validateForm.disabled ? true : this.validateForm.valid
  }

}
