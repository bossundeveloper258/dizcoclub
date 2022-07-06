import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { NzModalRef, NzModalService } from 'ng-zorro-antd/modal';
import CustomValidators from 'src/app/shared/utils/customValidators';
import { AuthService } from '../../services/auth.service';
import { StorageService } from '../../services/storage.service';

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css']
})
export class RegisterComponent implements OnInit {
  
  validateForm!: FormGroup;
  modalRef?: NzModalRef;
  loading: boolean = false;
  constructor(
    private auth: AuthService,
    private fb: FormBuilder,
    private modalService: NzModalService,
    private storageService: StorageService,
    private router: Router,
  ) { 
    
  }


  ngOnInit(): void {

    this.validateForm = this.fb.group(
      {
      name: ["", [Validators.required ]],
      email: ["", [Validators.required , Validators.email]],
      password: ["", [Validators.required]],
      confirmpassword: ["", [Validators.required]],
      dni: ["", [Validators.required, Validators.maxLength(8), Validators.pattern("^[0-9]*$")]]
    }, 
    { 
      validator : [CustomValidators.match('password', 'confirmpassword')]
    } 
    );
  }

  submitForm(){
    
    if (this.validateForm.invalid) {
      Object.values(this.validateForm.controls).forEach(control => {
        if (control.invalid) {
          control.markAsDirty();
          control.updateValueAndValidity({ onlySelf: true });
        }
      });
    }else{
      
      this.modalService.confirm({
        nzTitle: "Confirmar",
        nzContent: "Desea crear un usuario?",
        nzClosable: true,
        nzOkText: "Aceptar",
        nzOnOk: () => {
          this.loading = true;
          this.auth.resgiter(this.validateForm.value).subscribe(
            res => {
              this.modalService.confirm({
                nzTitle: "Creado",
                nzContent: "Usuario creado, porfavor ingresa tus credenciales para poder iniciar.",
                nzClosable: true,
                nzOkText: "Aceptar",
                nzOnOk: () => {
                  this.router.navigate(['/login'])
                }
              })
            }
          )
        }
      })
      
    }
  }

}
