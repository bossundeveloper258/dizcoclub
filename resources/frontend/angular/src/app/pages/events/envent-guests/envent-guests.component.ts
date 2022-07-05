import { Component, OnInit } from '@angular/core';
import { FormArray, FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { OrderService } from 'src/app/core/services/order.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { UserModel } from 'src/app/shared/models/auth.model';


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

  constructor(
    private fb: FormBuilder,
    private storageService: StorageService,
    private orderService: OrderService,
    private activatedRoute: ActivatedRoute,
  ) {

    this.activatedRoute.queryParams
      .subscribe(params => {
        console.log(params); // { orderby: "price" }
        this.session = params.session;
        this.purchaseNumber = params.purchaseNumber;
        this.merchantid = params.merchantid;
      }
    );

    this.quantity = parseInt(localStorage.getItem("q") ?? "0");

    this.user = this.storageService.getUser(); 

    this.validateForm = this.fb.group({
      guestList: this.fb.array([
        this.fb.group({
          name: [{value: this.user?.name ?? "" , disabled: this.user? true: false} , [Validators.required]],
          lastname: [{value: "" , disabled: this.user? true: false} , [Validators.required]],
          dni: [{value: this.user?.dni ?? "" , disabled: this.user? true: false} , [Validators.required]]
        })
      ])
    });
    if(this.quantity > 0){
      this.guestsArray = this.validateForm.get('guestList') as FormArray;

      let formGroup:FormGroup = this.fb.group({
        name: this.fb.control("" , [Validators.required]),
        lastname: this.fb.control("" , [Validators.required]),
        dni: this.fb.control("" , [Validators.required])
      });
      for (let i = 1; i <= this.quantity; i++) {
        this.guestsArray.push(formGroup);
      }
    }

  }

  ngOnInit(): void {
    
  }

  submitForm(){
    
  }

}
