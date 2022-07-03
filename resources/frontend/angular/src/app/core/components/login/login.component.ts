import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../services/auth.service';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { NzModalService , NzModalRef } from 'ng-zorro-antd/modal';
import { StorageService } from '../../services/storage.service';
import { Router } from '@angular/router';
import { environment } from 'src/environments/environment';
@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {

  validateForm!: FormGroup;
  modalRef?: NzModalRef;
  loading: boolean = false;
  routeAsset = environment.assetsUrl;
  
  constructor(
    private auth: AuthService,
    private fb: FormBuilder,
    private modalService: NzModalService,
    private storageService: StorageService,
    private router: Router,
  ) {
    this.validateForm = this.fb.group({
      email: [null, [Validators.required , Validators.email]],
      password: [null, [Validators.required]]
    });
   }

  ngOnInit(): void {
  }

  submitForm(){
    if (this.validateForm.valid) {
      this.loading = true;
      this.auth.login( this.validateForm.get('email')?.value , this.validateForm.get('password')?.value )
        .subscribe(
          res => {
            this.loading = false;
            this.storageService.setToken(res.token);
            this.storageService.setUser(res.user);
            this.router.navigate(['/events'])
          },
          (error) => {
            this.modalRef = this.modalService.error({
              nzTitle: "Error",
              nzContent: error.message,
              nzOkText: "Cancelar"
            })
          }
        )
    } 
    
  }

}
