import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../services/auth.service';
import { Router, ActivatedRoute } from '@angular/router';
import { StorageService } from '../../services/storage.service';
import { NzModalService , NzModalRef } from 'ng-zorro-antd/modal';
import { UserModel } from 'src/app/shared/models/auth.model';

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css']
})
export class HeaderComponent implements OnInit {

  user?: UserModel;

  constructor(
    private authService: AuthService,
    private router: Router,
    private storageService: StorageService,
    private modalService: NzModalService
  ) { 
    this.user = this.storageService.getUser();
    console.log(this.user, "=======")
  }

  ngOnInit(): void {
  }

  public logout(): void{
    this.modalService.confirm({
      nzTitle: "Cerrar Sesión",
      nzContent: "¿Desea terminar la session?",
      nzOnOk: () => {
        this.authService.logout().subscribe(
          res => {
            this.storageService.removeStorage();
            this.router.navigate(['/login'])
          }
        )
      },
      nzOkText: "Aceptar"
    })
    
  }

}
