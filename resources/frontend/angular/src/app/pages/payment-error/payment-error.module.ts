import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PaymentErrorComponent } from './payment-error.component';
import { PaymentErrorRoutingModule } from './payment-error-routing.module';



@NgModule({
  declarations: [
    PaymentErrorComponent
  ],
  imports: [
    CommonModule,
    PaymentErrorRoutingModule
  ]
})
export class PaymentErrorModule { }
