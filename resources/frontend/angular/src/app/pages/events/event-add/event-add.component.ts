import { Component, OnInit } from '@angular/core';
import { Observable, Observer } from 'rxjs';

import { NzMessageService } from 'ng-zorro-antd/message';
import { NzUploadChangeParam, NzUploadFile } from 'ng-zorro-antd/upload';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';

import { differenceInCalendarDays, setHours } from 'date-fns';
import { EventService } from 'src/app/core/services/event.service';
import * as moment from 'moment'
import { NzModalService } from 'ng-zorro-antd/modal';
import { Router } from '@angular/router';

@Component({
  selector: 'app-event-add',
  templateUrl: './event-add.component.html',
  styleUrls: ['./event-add.component.css']
})
export class EventAddComponent implements OnInit {

  date = new Date();
  time = new Date("2021-01-01 00:00");
  validateForm!: FormGroup;
  isdiscount: boolean = false;
  today = new Date();

  previewImage: string | undefined = '';
  previewVisible = false;
  avatarFile: any;

  disabledDate = (current: Date): boolean => differenceInCalendarDays(current, this.today) < 0;

  // file
  loading = false;
  avatarUrl?: string;
  fileList: NzUploadFile[] = [];
  constructor(
    private msg: NzMessageService,
    private fb: FormBuilder,
    private eventService: EventService,
    private modalService: NzModalService,
    private router: Router
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

    this.modalService.success({
      nzTitle: "Creado",
      nzContent: "Creado correctamente",
      nzClosable: false,
      nzOkText: "Aceptar",
      nzOnOk: () => {
        this.router.navigate(['/events'])
      }
    })
  }

  onChange(result: Date): void {
    console.log('onChange: ', result);
  }

  onChangeDiscount(is: any){
    this.isdiscount = is;
  }


  // beforeUpload = (file: NzUploadFile, _fileList: NzUploadFile[]): Observable<boolean> =>
  //   new Observable((observer: Observer<boolean>) => {
  //     const isJpgOrPng = file.type === 'image/jpeg' || file.type === 'image/png';
  //     if (!isJpgOrPng) {
  //       this.msg.error('You can only upload JPG file!');
  //       observer.complete();
  //       return;
  //     }
  //     const isLt2M = file.size! / 1024 / 1024 < 2;
  //     if (!isLt2M) {
  //       this.msg.error('Image must smaller than 2MB!');
  //       observer.complete();
  //       return;
  //     }
  //     observer.next(isJpgOrPng && isLt2M);
  //     observer.complete();
  //   });

  private getBase64CallBack(img: File, callback: (img: string) => void): void {
    const reader = new FileReader();
    reader.addEventListener('load', () => callback(reader.result!.toString()));
    reader.readAsDataURL(img);
  }

  private getBase64 = (file: File): Promise<string | ArrayBuffer | null> =>
  new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = error => reject(error);
  });

  handleChange(info: { file: NzUploadFile }): void {
    this.avatarFile = info.file!.originFileObj!;
    this.getBase64CallBack(info.file!.originFileObj!, (img: string) => {
      this.loading = false;
      this.avatarUrl = img;
    });
  }

  beforeUpload = (file: NzUploadFile): boolean => {
    
    this.fileList = this.fileList.concat(file);
    return true;
  };

  handlePreview = async (file: NzUploadFile): Promise<void> => {
    console.log(file)
    if (!file.url && !file.preview) {
      file.preview = await this.getBase64(file.originFileObj!);
    }
    this.previewImage = file.url || file.preview;
    this.previewVisible = true;
  };

  submitForm(){
    if (this.validateForm.valid) {
      const  formData = new FormData();
      formData.append("title", this.validateForm.get('title')?.value);
      formData.append("date", moment(this.validateForm.get('date')?.value).format('YYYY-MM-DD'));
      formData.append("time", moment(this.validateForm.get('time')?.value).format('HH:mm'));
      formData.append("address", this.validateForm.get('address')?.value);
      formData.append("description", this.validateForm.get('description')?.value);
      formData.append("stock", this.validateForm.get('stock')?.value);
      formData.append("price", this.validateForm.get('price')?.value);
      formData.append("isdiscount", this.validateForm.get('isdiscount')?.value ? "1" : "0" );
      if(this.isdiscount){
        formData.append("discount", this.validateForm.get('discount')?.value);
        formData.append("discount_stock", this.validateForm.get('discount_stock')?.value);
      }

      if(this.avatarFile){
        formData.append("file", this.avatarFile);
      }else{
        this.msg.error("Subir una imagen como avatar")
      }

      this.fileList.forEach((file: any) => {
        formData.append('files[]', file);
      });

      console.log(formData);
      this.eventService.postCreate(formData).subscribe(
        res => {
          this.modalService.success({
            nzTitle: "Creado",
            nzContent: "Creado correctamente"
          })
          
        },
        error => {
          console.log(error)
          this.modalService.error({
            nzTitle: "Error",
            nzContent: error
          })
        }
      )
    }
    
  }

}
