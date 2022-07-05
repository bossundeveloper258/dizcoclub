import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { EventAddComponent } from './event-add/event-add.component';
import { EventDetailComponent } from './event-detail/event-detail.component';
import { EventsComponent } from './events.component';

const routes: Routes = [
    { path: '', component: EventsComponent},
    { path: 'add', component: EventAddComponent},
    { path: 'detail/:id', component: EventDetailComponent}
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class EventRoutingModule { }