import { Component, OnInit } from '@angular/core';
import { Observable, Observer } from 'rxjs';

import { NzMessageService } from 'ng-zorro-antd/message';
import { NzUploadFile } from 'ng-zorro-antd/upload';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';

@Component({
  selector: 'app-event-add',
  templateUrl: './event-add.component.html',
  styleUrls: ['./event-add.component.css']
})
export class EventAddComponent implements OnInit {

  date = null;
  time = new Date();
  validateForm!: FormGroup;
  isdiscount: boolean = false;

  // file
  loading = false;
  avatarUrl?: string;

  constructor(
    private msg: NzMessageService,
    private fb: FormBuilder
  ) {
    this.validateForm = this.fb.group({
      title: [null, [ Validators.required]],
      date: [null, [Validators.required]],
      time: [null, [Validators.required]],
      address: [null, [Validators.required]],
      description: [null, [Validators.required]],
      stock: [null, [Validators.required]],
      price: [null, [Validators.required]],
      isdiscount: [false],
      discount: [null],
      discount_stock: [null]
    });
  }

  ngOnInit(): void {
  }

  onChange(result: Date): void {
    console.log('onChange: ', result);
  }


  beforeUpload = (file: NzUploadFile, _fileList: NzUploadFile[]): Observable<boolean> =>
    new Observable((observer: Observer<boolean>) => {
      const isJpgOrPng = file.type === 'image/jpeg' || file.type === 'image/png';
      if (!isJpgOrPng) {
        this.msg.error('You can only upload JPG file!');
        observer.complete();
        return;
      }
      const isLt2M = file.size! / 1024 / 1024 < 2;
      if (!isLt2M) {
        this.msg.error('Image must smaller than 2MB!');
        observer.complete();
        return;
      }
      observer.next(isJpgOrPng && isLt2M);
      observer.complete();
    });

  private getBase64(img: File, callback: (img: string) => void): void {
    const reader = new FileReader();
    reader.addEventListener('load', () => callback(reader.result!.toString()));
    reader.readAsDataURL(img);
  }

  handleChange(info: { file: NzUploadFile }): void {
    switch (info.file.status) {
      case 'uploading':
        this.loading = true;
        break;
      case 'done':
        // Get this url from response in real world.
        this.getBase64(info.file!.originFileObj!, (img: string) => {
          this.loading = false;
          this.avatarUrl = img;
        });
        break;
      case 'error':
        this.msg.error('Network error');
        this.loading = false;
        break;
    }
  }

}
