import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { MainLayoutComponent } from './core/components/main-layout/main-layout.component';
import { MainComponent } from './core/components/main/main.component';
import { AuthGuardService } from './core/services/guard/auth-guard.service';


const routes: Routes = [
  {
    path: '',
    component: MainComponent,
    children: [
        {
            path: '',
            loadChildren: () => import('./pages/home/home.module').then(m => m.HomeModule)
        },
        {
          path: 'validate-token',
          loadChildren: () => import('./pages/validate-token/validate-token.module').then(m => m.ValidateTokenModule)
        }
    ]
  },
  {
    path: '',
    component: MainLayoutComponent,
    children: [
        {
            path: 'events',
            loadChildren: () => import('./pages/events/events.module').then(m => m.EventsModule)
        },
        {
          path: 'profile',
          loadChildren: () => import('./pages/profile/profile.module').then(m => m.ProfileModule)
        },
        {
          path: 'tickets',
          loadChildren: () => import('./pages/tickets/tickets.module').then(m => m.TicketsModule)
        },
        
    ]
  },
  {
    path: 'payment-success/:token',
    component: MainComponent,
    children: [
        {
            path: '',
            loadChildren: () => import('./pages/payment-success/payment-success.module').then(m => m.PaymentSuccessModule)
        }
    ]
  },
];

@NgModule({
  imports: [ 
    RouterModule.forRoot(routes)
    
],
  exports: [RouterModule]
})
export class AppRoutingModule { }
