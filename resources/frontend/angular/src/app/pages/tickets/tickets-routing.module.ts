import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { NotAuthService } from 'src/app/core/services/guard/not-auth.service';
import { TicketsComponent } from './tickets.component';


const routes: Routes = [
    { path: '', component: TicketsComponent, canActivate:[NotAuthService]},
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class TicketsRoutingModule { }