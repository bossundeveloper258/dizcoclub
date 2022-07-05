import { NgModule } from '@angular/core';

import { CommonModule } from '@angular/common';
import { EventsComponent } from './events.component';
import { EventRoutingModule } from './events-routing.module';
import { SharedModule } from 'src/app/shared/shared.module';
import { EventAddComponent } from './event-add/event-add.component';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import {HttpClientModule} from '@angular/common/http';
import { DragDropModule } from '@angular/cdk/drag-drop';
import { ScrollingModule } from '@angular/cdk/scrolling';
import { NzToolTipModule } from 'ng-zorro-antd/tooltip';
import { EventDetailComponent } from './event-detail/event-detail.component';

@NgModule({
  declarations: [
    EventsComponent,
    EventAddComponent,
    EventDetailComponent
  ],
  imports: [
    CommonModule,
    SharedModule,
    HttpClientModule,
    FormsModule,
    ReactiveFormsModule,
    EventRoutingModule,
    DragDropModule
  ]
})
export class EventsModule { }
