import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { AuthGuardService } from 'src/app/core/services/guard/auth-guard.service';
import { NotAuthService } from 'src/app/core/services/guard/not-auth.service';
import { ValidateTokenComponent } from './validate-token.component';

const routes: Routes = [
    { path: ':token', component: ValidateTokenComponent, canActivate:[NotAuthService]},
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ValidateTokenRoutingModule { }