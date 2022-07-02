import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { EventsComponent } from './events.component';
import { EventRoutingModule } from './events-routing.module';
import { SharedModule } from 'src/app/shared/shared.module';
import { EventAddComponent } from './event-add/event-add.component';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import {HttpClientModule} from '@angular/common/http';

@NgModule({
  declarations: [
    EventsComponent,
    EventAddComponent
  ],
  imports: [
    CommonModule,
    SharedModule,
    HttpClientModule,
    FormsModule,
    ReactiveFormsModule,
    EventRoutingModule
  ]
})
export class EventsModule { }
