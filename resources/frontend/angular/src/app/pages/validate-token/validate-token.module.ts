import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ValidateTokenComponent } from './validate-token.component';
import { ValidateTokenRoutingModule } from './validate-token-routing.module';



@NgModule({
  declarations: [
    ValidateTokenComponent
  ],
  imports: [
    CommonModule,
    ValidateTokenRoutingModule
  ]
})
export class ValidateTokenModule { }
