import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { NgZorroModule } from '../ng-zorro.module';
import { DropzoneDirective } from './directives/dropzone.directive';

@NgModule({
  declarations: [
    DropzoneDirective
  ],
  imports: [
    CommonModule,
    NgZorroModule
  ],
  exports: [
    NgZorroModule
  ]
})
export class SharedModule { }
