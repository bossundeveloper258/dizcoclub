import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { NzModalService } from 'ng-zorro-antd/modal';
import { AuthService } from 'src/app/core/services/auth.service';
import { StorageService } from 'src/app/core/services/storage.service';
import { UserModel } from 'src/app/shared/models/auth.model';
import { environment } from 'src/environments/environment';
@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.css']
})
export class HomeComponent implements OnInit {

  routeAsset = environment.assetsUrl;
  playlist: boolean = false;
  user!: UserModel;
  constructor(
    private storageService: StorageService,
    private modalService: NzModalService,
    private authService: AuthService,
    private router: Router,
  ) { 
    this.user = this.storageService.getUser();
  }

  ngOnInit(): void {
  }

  onViewPlaylist(): void{
    this.playlist = !this.playlist;
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
