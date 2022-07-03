import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { LoginComponent } from './components/login/login.component';
import { AuthGuardService } from './services/guard/auth-guard.service';

const routes: Routes = [
    {
        path: 'login',
        component: LoginComponent,
        canActivate: [AuthGuardService]
    }
];

@NgModule({
  imports: [ 
    RouterModule.forRoot(routes),
],
  exports: [RouterModule]
})
export class CoreRoutingModule { }