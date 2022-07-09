import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { PaymentErrorComponent } from './payment-error.component';



const routes: Routes = [
    { path: '', component: PaymentErrorComponent},
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class PaymentErrorRoutingModule { }