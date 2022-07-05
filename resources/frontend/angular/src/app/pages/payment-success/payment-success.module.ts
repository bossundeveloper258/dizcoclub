import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PaymentSuccessComponent } from './payment-success.component';
import { PaymentSuccessRoutingModule } from './payment-success-routing.module';



@NgModule({
  declarations: [
    PaymentSuccessComponent
  ],
  imports: [
    CommonModule,
    PaymentSuccessRoutingModule
  ]
})
export class PaymentSuccessModule { }
