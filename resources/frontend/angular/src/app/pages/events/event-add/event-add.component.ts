import { Component, OnInit } from '@angular/core';
import { Observable, Observer } from 'rxjs';

import { NzMessageService } from 'ng-zorro-antd/message';
import { NzUploadChangeParam, NzUploadFile } from 'ng-zorro-antd/upload';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';

import { differenceInCalendarDays, setHours } from 'date-fns';
import { EventService } from 'src/app/core/services/event.service';
import * as moment from 'moment'
import { NzModalService, NzModalRef } from 'ng-zorro-antd/modal';
import { Router } from '@angular/router';
import { DomSanitizer } from '@angular/platform-browser';
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
  modalRef?: NzModalRef;
  // file
  loading = false;
  avatarUrl?: string;
  fileList: NzUploadFile[] = [];

  loadingBtn: boolean = false;

  isHovering: boolean = false;
 
  files: File[] = [];

  // ========================================

  fileArr: any[] = [];
  imgArr: string[] = [];
  fileObj: any[] = [];

  constructor(
    private msg: NzMessageService,
    private fb: FormBuilder,
    private eventService: EventService,
    private modalService: NzModalService,
    private router: Router,
    private sanitizer: DomSanitizer,
  ) {
    this.validateForm = this.fb.group({
      title: [null, [ Validators.required]],
      date: [null, [Validators.required]],
      time: [null, [Validators.required]],
      address: [null, [Validators.required]],
      description: [null, [Validators.required]],
      stock: [30, [Validators.required]],
      price: [50, [Validators.required]],
      isdiscount: [false],
      discount: [null],
      discount_stock: [null]
    });
  }

  
 
  toggleHover(event: any) {
    this.isHovering = event;
  }
 
  upload(e: any) {
    const fileListAsArray = Array.from(e);
    fileListAsArray.forEach((item, i) => {
      const file = (e as HTMLInputElement);
      const url = URL.createObjectURL(e[i]);
      this.imgArr.push(url);
      this.fileArr.push({ item, url: url });
    })
    this.fileArr.forEach((item) => {
      this.fileObj.push(item.item)
    })
    
  }

  sanitize(url: string) {
    return this.sanitizer.bypassSecurityTrustUrl(url);
  }

  ngOnInit(): void {

    
  }

  onChange(result: Date): void {
    console.log('onChange: ', result);
  }

  onChangeDiscount(is: any){
    this.isdiscount = is;
    if(is){
      this.validateForm.get('discount')?.setValidators([Validators.required])
      this.validateForm.get('discount_stock')?.setValidators([Validators.required])
    }else{
      this.validateForm.get('discount')?.clearValidators();
      this.validateForm.get('discount_stock')?.clearValidators();
    }

    
  }

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
      
      this.modalRef = this.modalService.confirm({
        nzTitle: "Crear Evento",
        nzContent: "¿Desea crear el Evento "+ this.validateForm.get('title')?.value+"?",
        nzClosable: true,
        nzOkText: "Aceptar",
        nzOnOk: () => {
          this.modalRef?.close();
          this.loadingBtn = true;
          try {
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

              this.fileObj.forEach((file: any) => {
                formData.append('files[]', file);
              });
        
              this.eventService.postCreate(formData).subscribe(
                res => {
                  this.loadingBtn = false;
                  this.modalService.success({
                    nzTitle: "Creado",
                    nzContent: "Creado correctamente",
                    nzClosable: false,
                    nzOkText: "Aceptar",
                    nzOnOk: () => {
                      this.router.navigate(['/events']);
                    }
                  })
                },
                error => {
                  console.log(error)
                  this.modalService.error({
                    nzTitle: "Error",
                    nzContent: error.message
                  })
                  this.loadingBtn = false;
                }
              )

            }else{
              this.msg.error("Subir una imagen como avatar")
            }
          } catch (error) {
            this.loadingBtn = false;
          }
          
        }
      })

    }else{
      Object.values(this.validateForm.controls).forEach(control => {
        if (control.invalid) {
          control.markAsDirty();
          control.updateValueAndValidity({ onlySelf: true });
        }
      });
    }
    
  }

}
