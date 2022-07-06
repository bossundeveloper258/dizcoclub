import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { NgZorroModule } from '../ng-zorro.module';
import { DropzoneDirective } from './directives/dropzone.directive';
import { LoadingComponent } from './components/loading/loading.component';

@NgModule({
  declarations: [
    DropzoneDirective,
    LoadingComponent
  ],
  imports: [
    CommonModule,
    NgZorroModule
  ],
  exports: [
    NgZorroModule,
    LoadingComponent
  ]
})
export class SharedModule { }
