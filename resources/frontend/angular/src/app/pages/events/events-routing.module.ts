import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { EventAddComponent } from './event-add/event-add.component';
import { EventsComponent } from './events.component';

const routes: Routes = [
    { path: '', component: EventsComponent},
    { path: 'add', component: EventAddComponent}
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class EventRoutingModule { }