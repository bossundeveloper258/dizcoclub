import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ValidateTokenComponent } from './validate-token.component';
import { ValidateTokenRoutingModule } from './validate-token-routing.module';
import { SharedModule } from 'src/app/shared/shared.module';



@NgModule({
  declarations: [
    ValidateTokenComponent
  ],
  imports: [
    CommonModule,
    SharedModule,
    ValidateTokenRoutingModule
  ]
})
export class ValidateTokenModule { }
